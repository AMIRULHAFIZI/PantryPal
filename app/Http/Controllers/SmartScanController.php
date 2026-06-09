<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\PantryItem;
use App\Models\ReceiptScan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SmartScanController extends Controller
{
    public function index()
    {
        $totalFound = auth()->user()->receiptScans()->sum('total_items_found');
        $totalExtracted = auth()->user()->receiptScans()->sum('items_extracted');
        $overallPercentage = $totalFound > 0 ? round(($totalExtracted / $totalFound) * 100) : 0;
        
        return view('smart-scan.index', compact('overallPercentage'));
    }

    public function uploadReceipt(Request $request)
    {
        set_time_limit(120);
        ini_set('memory_limit', '256M');

        $request->validate([
            'receipt' => 'required|mimes:jpeg,jpg,png,gif,bmp,webp,heic,heif|max:10240',
        ]);

        Log::info('SmartScan receipt upload. Mime: ' . $request->file('receipt')->getMimeType() . ', Ext: ' . $request->file('receipt')->getClientOriginalExtension());

        $imagePath = $request->file('receipt')->store('receipts', 'public');
        $imageContents = file_get_contents(storage_path('app/public/' . $imagePath));
        $base64Image = base64_encode($imageContents);
        $mimeType = $request->file('receipt')->getMimeType();

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return back()->with('error', 'Gemini API Key is missing. Please configure it in .env.');
        }

        $prompt = 'You are an AI grocery assistant. Analyze this receipt image and extract each line item into JSON. CRITICAL RULES: (1) The "item_name" field must contain ONLY the clean product name — never include quantities, counts, weights, or units like "(4pc)", "4x", "2pcs" in the name. (2) Put the numeric quantity in the "quantity" field. (3) For quantity: use the numeric amount on the receipt (e.g. 0.848 for weight-sold items, 4 if the receipt shows 4 pieces). (4) For unit pick from: "pcs", "kg", "g", "L", "ml", "pack", "box", "bottle", "can", "bag". (5) Count total distinct line items. Return ONLY a valid JSON object exactly like: {"total_receipt_items": 12, "items": [{"item_name": "Cheezy Samosa", "quantity": 4, "unit": "pcs", "category": "Snacks"}]}';
        
        $response = Http::timeout(120)->withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Image,
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            $textOutput = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            $textOutput = trim($textOutput);
            if (str_starts_with($textOutput, '```json')) {
                $textOutput = str_replace(['```json', '```'], '', $textOutput);
                $textOutput = trim($textOutput);
            }

            $data = json_decode($textOutput, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($data) && isset($data['items'])) {
                $items = $data['items'];
                $totalFoundOnReceipt = $data['total_receipt_items'] ?? count($items);
                
                $addedItemsCount = 0;
                foreach ($items as $item) {
                    $itemName = $item['item_name'] ?? 'Unknown Item';
                    $quantity  = (float)($item['quantity'] ?? 1);

                    if (preg_match('/^\(?\s*(\d+(?:\.\d+)?)\s*(?:pc|pcs|x|kg|g|L|ml|pack|box|bottle|can|bag)?\s*\)?\s*/i', $itemName, $matches)) {
                        $embeddedQty = (float)$matches[1];
                        if ($embeddedQty > 0 && $embeddedQty <= 100) {
                            $quantity  = $embeddedQty;
                            $itemName  = trim(preg_replace('/^\(?\s*\d+(?:\.\d+)?\s*(?:pc|pcs|x|kg|g|L|ml|pack|box|bottle|can|bag)?\s*\)?\s*/i', '', $itemName));
                        }
                    }

                    auth()->user()->pantryItems()->create([
                        'item_name'   => $itemName,
                        'quantity'    => $quantity,
                        'unit'        => $item['unit'] ?? 'pcs',
                        'category'    => $item['category'] ?? 'Other',
                        'expiry_date' => null,
                    ]);
                    $addedItemsCount++;
                }

                $currentPercentage = $totalFoundOnReceipt > 0 ? round(($addedItemsCount / $totalFoundOnReceipt) * 100) : 100;
                if ($currentPercentage > 100) $currentPercentage = 100;

                auth()->user()->receiptScans()->create([
                    'total_items_found' => max($totalFoundOnReceipt, $addedItemsCount),
                    'items_extracted' => $addedItemsCount
                ]);

                return back()->with('success', "Successfully extracted and added {$addedItemsCount} item(s) to your pantry!")
                             ->with('currentPercentage', $currentPercentage);
            } else {
                $items = is_array($data) ? $data : [];
                if (count($items) > 0 && isset($items[0]['item_name'])) {
                    $addedItemsCount = 0;
                    foreach ($items as $item) {
                        auth()->user()->pantryItems()->create([
                            'item_name' => $item['item_name'] ?? 'Unknown Item',
                            'quantity'  => (float)($item['quantity'] ?? 1),
                            'unit'      => $item['unit'] ?? 'pcs',
                            'category'  => $item['category'] ?? 'Other',
                            'expiry_date' => null,
                        ]);
                        $addedItemsCount++;
                    }
                    
                    auth()->user()->receiptScans()->create([
                        'total_items_found' => $addedItemsCount,
                        'items_extracted' => $addedItemsCount
                    ]);

                    return back()->with('success', "Successfully extracted and added {$addedItemsCount} item(s) to your pantry!")
                                 ->with('currentPercentage', 100);
                }

                Log::error('Gemini API JSON Parse Error: ' . $textOutput);
                return back()->with('error', 'Could not parse the receipt data correctly. AI raw response logged.');
            }
        }

        $responseStatus = $response->status();
        $responseBody = substr($response->body(), 0, 500);
        Log::error("Gemini API Receipt Error [{$responseStatus}]: " . $response->body());
        return back()->with('error', "Gemini API Error (HTTP {$responseStatus}): " . $responseBody);
    }

    public function updateItemExpiry(Request $request, PantryItem $pantryItem)
    {
        if ($pantryItem->user_id !== auth()->id()) abort(403);

        set_time_limit(120);
        ini_set('memory_limit', '256M');

        $request->validate([
            'expiry_image' => 'required|image|max:10240',
        ]);

        $imagePath = $request->file('expiry_image')->store('expiry_scans', 'public');
        $imageContents = file_get_contents(storage_path('app/public/' . $imagePath));
        $base64Image = base64_encode($imageContents);
        $mimeType = $request->file('expiry_image')->getMimeType();

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return back()->with('error', 'Gemini API Key is missing. Please configure it in .env.');
        }

        $prompt = 'Analyze this image of a product label or barcode. Find its expiration date. Return ONLY a JSON object exactly in this format: {"expiry_date": "YYYY-MM-DD"}. If you cannot find an expiration date clearly, return null for the date value.';
        
        $response = Http::timeout(120)->withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Image,
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            $textOutput = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            $textOutput = trim($textOutput);
            if (str_starts_with($textOutput, '```json')) {
                $textOutput = str_replace(['```json', '```'], '', $textOutput);
                $textOutput = trim($textOutput);
            }

            $data = json_decode($textOutput, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                $expiryDate = $data['expiry_date'] ?? null;

                if ($expiryDate) {
                    $pantryItem->update([
                        'expiry_date' => $expiryDate,
                    ]);
                    return back()->with('success', "Success! Updated expiry date for '{$pantryItem->item_name}' to {$expiryDate}.");
                } else {
                    return back()->with('error', "Could not clearly read the expiry date from the image. Please try adding it manually via Edit.");
                }
            }

            Log::error('Gemini API JSON Parse Error: ' . $textOutput);
            return back()->with('error', 'Could not clearly read the expiry date from the image. Please try again or add it manually.');
        }

        Log::error('Gemini API Error: ' . $response->body());
        return back()->with('error', 'Failed to communicate with Gemini AI. Please try again or add manually from the Dashboard.');
    }

    public function checkRipeness(Request $request)
    {
        set_time_limit(120);
        ini_set('memory_limit', '256M');

        $request->validate([
            'ripeness_image' => 'required|mimes:jpeg,jpg,png,gif,bmp,webp,heic,heif|max:10240',
        ]);

        $imagePath = $request->file('ripeness_image')->store('ripeness_scans', 'public');
        $imageContents = file_get_contents(storage_path('app/public/' . $imagePath));
        $base64Image = base64_encode($imageContents);
        $mimeType = $request->file('ripeness_image')->getMimeType();

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'Gemini API Key is missing.'], 500);
        }

        $prompt = 'You are a produce expert AI. Analyze this image of a fruit or vegetable and assess its ripeness. Return ONLY a valid JSON object in this exact format:
{
  "item_name": "Banana",
  "ripeness_level": "Ripe",
  "ripeness_score": 75,
  "color_description": "Yellow with a few brown spots",
  "shelf_life_days": 3,
  "recommendation": "Best eaten now or within 1-2 days. Great for smoothies if over-ripe.",
  "storage_tip": "Store at room temperature away from other fruits to slow further ripening.",
  "is_produce": true
}
Rules:
- "ripeness_level" must be one of: "Unripe", "Nearly Ripe", "Ripe", "Overripe", "Spoiled"
- "ripeness_score" is 0-100 where 0=unripe, 50=ripe, 100=spoiled
- "shelf_life_days" is your best estimate of days remaining before it should be consumed (0 if spoiled)
- "is_produce" must be false if the image does not show a fruit or vegetable
- If not a fruit/vegetable, set item_name to what you see and all other fields to null except is_produce: false';

        $response = Http::timeout(120)->withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data'      => $base64Image,
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            $textOutput = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $textOutput = trim($textOutput);
            if (str_starts_with($textOutput, '```json')) {
                $textOutput = str_replace(['```json', '```'], '', $textOutput);
                $textOutput = trim($textOutput);
            }

            $data = json_decode($textOutput, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return response()->json(['success' => true, 'result' => $data]);
            }

            Log::error('Ripeness API JSON Parse Error: ' . $textOutput);
            return response()->json(['error' => 'Could not parse ripeness data. Please try again.'], 422);
        }

        Log::error('Gemini Ripeness API Error: ' . $response->body());
        return response()->json(['error' => 'Failed to communicate with AI. Please try again.'], 500);
    }
}
