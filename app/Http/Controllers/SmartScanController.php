<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\PantryItem;
use App\Models\ReceiptScan;
use App\Models\RipenessScan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SmartScanController extends Controller
{
    public function index()
    {
        // ── Receipt stats ────────────────────────────────────────
        $totalFound = auth()->user()->receiptScans()->sum('total_items_found');
        $totalExtracted = auth()->user()->receiptScans()->sum('items_extracted');
        $overallPercentage = $totalFound > 0 ? round(($totalExtracted / $totalFound) * 100) : 0;

        $lastScan = auth()->user()->receiptScans()->latest()->first();
        $currentPercentage = null;
        if ($lastScan) {
            $currentPercentage = $lastScan->total_items_found > 0
                ? min(100, round(($lastScan->items_extracted / $lastScan->total_items_found) * 100))
                : 100;
        }

        // ── Ripeness stats ───────────────────────────────────────
        $totalRipenessScans    = auth()->user()->ripenessScans()->count();
        $succeededRipenessScans = auth()->user()->ripenessScans()->where('is_success', true)->count();

        $ripenessHistory = auth()->user()->ripenessScans()
            ->where('is_success', true)
            ->latest()
            ->take(6)
            ->get();

        return view('smart-scan.index', compact(
            'overallPercentage', 'currentPercentage',
            'totalRipenessScans', 'succeededRipenessScans',
            'ripenessHistory'
        ));
    }

    public function uploadReceipt(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '256M');

        $request->validate([
            'receipt' => 'required|mimes:jpeg,jpg,png,gif,bmp,webp,heic,heif|max:10240',
        ]);

        // Set cross-device scanning flag so other open windows can show the overlay
        Cache::put('user_' . auth()->id() . '_scanning', 'receipt', 120);

        try {

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

        $receiptPayload = [
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                    ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64Image]],
                ]
            ]]
        ];

        $response = null;
        foreach (['gemini-2.5-flash-lite', 'gemini-2.5-flash', 'gemini-2.0-flash-lite', 'gemini-2.0-flash'] as $model) {
            try {
                $response = Http::timeout(30)->withoutVerifying()->withHeaders(['Content-Type' => 'application/json'])
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", $receiptPayload);
                if ($response->successful()) break;
                $s = $response->status();
                Log::warning("Receipt scan model [{$model}] failed HTTP {$s}, trying next.");
                if (in_array($s, [429, 503])) sleep(1);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::warning("Receipt scan model [{$model}] connection timeout, trying next.");
                $response = null;
                continue;
            }
        }

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

        $responseStatus = $response ? $response->status() : 0;
        $responseBody = $response ? substr($response->body(), 0, 500) : 'no response';
        Log::error("Gemini API Receipt Error [{$responseStatus}]: " . ($response ? $response->body() : 'no response'));
        return back()->with('error', "Gemini API Error (HTTP {$responseStatus}): " . $responseBody);

        } finally {
            Cache::forget('user_' . auth()->id() . '_scanning');
        }
    }

    public function updateItemExpiry(Request $request, PantryItem $pantryItem)
    {
        if ($pantryItem->user_id !== auth()->id()) abort(403);

        set_time_limit(300);
        ini_set('memory_limit', '256M');

        $request->validate([
            'expiry_image' => 'required|image|max:10240',
        ]);

        $imagePath = $request->file('expiry_image')->store('expiry_scans', 'public');
        $fullStoragePath = storage_path('app/public/' . $imagePath);

        // ── Compress image before sending to Gemini ───────────────
        // Resize to max 1024px and re-encode as JPEG at 80% quality.
        // This turns a ~1.9 MB photo into ~150 KB, preventing API timeouts.
        $compressedBase64 = null;
        $mimeType = 'image/jpeg';
        if (function_exists('imagecreatefromstring')) {
            $rawBytes = file_get_contents($fullStoragePath);
            $src = @imagecreatefromstring($rawBytes);
            if ($src) {
                $origW = imagesx($src);
                $origH = imagesy($src);
                $maxDim = 1024;
                if ($origW > $maxDim || $origH > $maxDim) {
                    $ratio  = min($maxDim / $origW, $maxDim / $origH);
                    $newW   = (int)round($origW * $ratio);
                    $newH   = (int)round($origH * $ratio);
                    $dst    = imagecreatetruecolor($newW, $newH);
                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                    imagedestroy($src);
                    $src = $dst;
                }
                ob_start();
                imagejpeg($src, null, 80);
                $compressedBytes = ob_get_clean();
                imagedestroy($src);
                $compressedBase64 = base64_encode($compressedBytes);
            }
        }
        // Fall back to raw file if GD is unavailable
        if (!$compressedBase64) {
            $compressedBase64 = base64_encode(file_get_contents($fullStoragePath));
            $mimeType = $request->file('expiry_image')->getMimeType();
        }

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return back()->with('error', 'Gemini API Key is missing. Please configure it in .env.');
        }

        $prompt = 'Analyze this image of a product label or barcode. Find its expiration date. Return ONLY a JSON object exactly in this format: {"expiry_date": "YYYY-MM-DD"}. If you cannot find an expiration date clearly, return null for the date value.';

        $expiryPayload = [
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                    ['inline_data' => ['mime_type' => $mimeType, 'data' => $compressedBase64]],
                ]
            ]]
        ];

        $response = null;
        foreach (['gemini-2.5-flash-lite', 'gemini-2.5-flash', 'gemini-2.0-flash-lite', 'gemini-2.0-flash'] as $model) {
            try {
                $response = Http::timeout(30)->withoutVerifying()->withHeaders(['Content-Type' => 'application/json'])
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", $expiryPayload);
                if ($response->successful()) break;
                $s = $response->status();
                Log::warning("Expiry scan model [{$model}] failed HTTP {$s}, trying next.");
                if (in_array($s, [429, 503])) sleep(1);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::warning("Expiry scan model [{$model}] connection timeout, trying next.");
                $response = null;
                continue;
            }
        }

        if ($response && $response->successful()) {
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

        Log::error('Gemini API Expiry Error: all models failed or timed out.');
        return back()->with('error', 'The AI took too long to respond. Please try again with a clearer, closer photo of the expiry date label.');
    }

    public function checkRipeness(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '256M');

        $request->validate([
            'ripeness_image' => 'required|mimes:jpeg,jpg,png,gif,bmp,webp,heic,heif|max:10240',
        ]);

        // Set cross-device scanning flag so other open windows can show the overlay
        Cache::put('user_' . auth()->id() . '_scanning', 'ripeness', 120);

        try {

        $imagePath = $request->file('ripeness_image')->store('ripeness_scans', 'public');
        $fullStoragePath = storage_path('app/public/' . $imagePath);

        // ── Compress image before sending to Gemini ───────────────
        // Resize to max 1024px and re-encode as JPEG at 80% quality.
        // A typical phone fruit photo is 2–5 MB; this brings it down to ~150 KB.
        $compressedBase64 = null;
        $mimeType = 'image/jpeg';
        if (function_exists('imagecreatefromstring')) {
            $rawBytes = file_get_contents($fullStoragePath);
            $src = @imagecreatefromstring($rawBytes);
            if ($src) {
                $origW = imagesx($src);
                $origH = imagesy($src);
                $maxDim = 1024;
                if ($origW > $maxDim || $origH > $maxDim) {
                    $ratio = min($maxDim / $origW, $maxDim / $origH);
                    $newW  = (int)round($origW * $ratio);
                    $newH  = (int)round($origH * $ratio);
                    $dst   = imagecreatetruecolor($newW, $newH);
                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                    imagedestroy($src);
                    $src = $dst;
                }
                ob_start();
                imagejpeg($src, null, 80);
                $compressedBytes = ob_get_clean();
                imagedestroy($src);
                $compressedBase64 = base64_encode($compressedBytes);
            }
        }
        // Fall back to raw file if GD is unavailable
        if (!$compressedBase64) {
            $compressedBase64 = base64_encode(file_get_contents($fullStoragePath));
            $mimeType = $request->file('ripeness_image')->getMimeType();
        }

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            auth()->user()->ripenessScans()->create(['image_path' => $imagePath, 'is_success' => false]);
            return response()->json(['error' => 'Gemini API Key is missing.'], 500);
        }

        $prompt = 'You are a produce expert AI. Carefully analyze the visual appearance of this fruit or vegetable image and assess its ripeness with precision. Return ONLY a valid JSON object in this exact format:
{
  "item_name": "Banana",
  "ripeness_level": "Ripe",
  "ripeness_score": 55,
  "color_description": "Fully yellow with no green, slight browning beginning at tips",
  "shelf_life_days": 3,
  "recommendation": "Best eaten now or within 1-2 days.",
  "storage_tip": "Store at room temperature away from other fruits.",
  "is_produce": true
}

STRICT RULES — follow exactly:

1. "ripeness_level" must be one of: "Unripe", "Nearly Ripe", "Ripe", "Overripe", "Spoiled"

2. "ripeness_score" scale — match visuals carefully:
   - 0–20  = Unripe (fully green/uncolored, very firm, immature)
   - 21–40 = Nearly Ripe (beginning to color, still firm)
   - 41–60 = Ripe (fully colored, ideal texture, ready to eat)
   - 61–80 = Overripe (soft spots, heavy browning/blackening, bruising, mushy)
   - 81–100 = Spoiled (mold, rot, foul appearance, inedible)

3. "shelf_life_days" MUST strictly match the ripeness_level — NO exceptions:
   - Unripe      → 10 to 14 days
   - Nearly Ripe → 5 to 9 days
   - Ripe        → 2 to 4 days
   - Overripe    → 0 to 1 day  ← MANDATORY if you see heavy browning/blackening, soft spots, or bruising
   - Spoiled     → 0 days

4. OVERRIPE DETECTION — classify as Overripe (shelf_life_days ≤ 1) if ANY of these are visible:
   - Dark brown or black patches covering >30% of skin/surface
   - Visible softness, collapse, or wrinkling
   - Heavy bruising or discoloration
   - Leaking juice or broken skin from softness

5. CALIBRATION EXAMPLES by fruit/vegetable:

   BANANA:
   - All green → Unripe, score 10, shelf 12 days
   - Green-yellow mix → Nearly Ripe, score 30, shelf 7 days
   - Fully yellow, no spots → Ripe, score 50, shelf 3 days
   - Yellow with scattered brown spots → Ripe, score 60, shelf 2 days
   - Mostly brown/black skin, very soft → Overripe, score 72, shelf 1 day
   - Completely black, leaking → Spoiled, score 90, shelf 0 days

   APPLE:
   - Pale green, very firm → Unripe, score 15, shelf 12 days
   - Mostly green, slightly firm → Nearly Ripe, score 35, shelf 6 days
   - Vibrant red/green/yellow, crisp → Ripe, score 50, shelf 4 days
   - Soft spots, skin wrinkling, dull colour → Overripe, score 65, shelf 1 day
   - Brown flesh showing, sunken areas, mold → Spoiled, score 85, shelf 0 days

   MANGO:
   - Very firm, green, no give → Unripe, score 10, shelf 14 days
   - Slightly soft at tip, green-yellow → Nearly Ripe, score 35, shelf 7 days
   - Gives slightly to pressure, golden/red skin → Ripe, score 50, shelf 3 days
   - Very soft, skin wrinkling, oversweet fermented smell → Overripe, score 70, shelf 1 day
   - Black spots, mold, foul smell → Spoiled, score 88, shelf 0 days

   STRAWBERRY:
   - White/pale pink, firm → Unripe, score 10, shelf 10 days
   - Light red, partially colored → Nearly Ripe, score 30, shelf 5 days
   - Bright deep red, glossy, firm → Ripe, score 50, shelf 2 days
   - Dull red, soft, mushy spots → Overripe, score 68, shelf 1 day
   - Mold, rot, collapsing → Spoiled, score 88, shelf 0 days

   AVOCADO:
   - Bright green, rock hard → Unripe, score 10, shelf 10 days
   - Dark green, slight give → Nearly Ripe, score 35, shelf 5 days
   - Dark purple-black, yields gently to pressure → Ripe, score 50, shelf 2 days
   - Very mushy, dented, stringy inside → Overripe, score 70, shelf 1 day
   - Brown flesh throughout, rancid smell → Spoiled, score 88, shelf 0 days

   ORANGE / MANDARIN:
   - Pale green, hard → Unripe, score 10, shelf 14 days
   - Green-orange, firm → Nearly Ripe, score 32, shelf 8 days
   - Deep orange, firm, fragrant → Ripe, score 50, shelf 4 days
   - Soft, spongy skin, dull and shrivelling → Overripe, score 65, shelf 1 day
   - Mold, fully shriveled, rotten spots → Spoiled, score 85, shelf 0 days

   GRAPES:
   - Hard, tart, pale-coloured → Unripe, score 10, shelf 12 days
   - Slightly soft, moderate colour → Nearly Ripe, score 32, shelf 7 days
   - Plump, full colour, firm skin → Ripe, score 50, shelf 4 days
   - Wrinkled skin, very soft, falling off stem → Overripe, score 67, shelf 1 day
   - Mold, fermenting, mushy → Spoiled, score 86, shelf 0 days

   TOMATO:
   - Fully green, very firm → Unripe, score 10, shelf 12 days
   - Green with hint of orange/red → Nearly Ripe, score 32, shelf 6 days
   - Deep red, firm, fragrant → Ripe, score 50, shelf 3 days
   - Very soft, skin wrinkling or cracking → Overripe, score 67, shelf 1 day
   - Mold, rotting, leaking → Spoiled, score 87, shelf 0 days

   WATERMELON:
   - White/pale yellow field spot, hard rind → Unripe, score 12, shelf 14 days
   - Creamy yellow field spot → Nearly Ripe, score 35, shelf 7 days
   - Deep yellow field spot, firm, resonant thud → Ripe, score 50, shelf 4 days
   - Soft dented rind, overripe fermented smell → Overripe, score 65, shelf 1 day
   - Sunken, slimy, foul smell → Spoiled, score 88, shelf 0 days

   PINEAPPLE:
   - Fully green, very hard, no sweetness → Unripe, score 12, shelf 10 days
   - Partially yellow base, slight sweetness → Nearly Ripe, score 35, shelf 5 days
   - Golden yellow base, fragrant, slight give → Ripe, score 50, shelf 3 days
   - Very soft, heavily fermented/alcohol smell → Overripe, score 68, shelf 1 day
   - Mold on eyes/crown, slimy base → Spoiled, score 87, shelf 0 days

   FOR ALL OTHER PRODUCE: apply the same pattern — score and shelf life must be consistent with the visual ripeness level observed.

6. "is_produce" must be false if the image does not show a fruit or vegetable.
   If not produce, set all other fields to null except is_produce: false.';

        $models = [
            'gemini-2.5-flash-lite',
            'gemini-2.5-flash',
            'gemini-2.0-flash-lite',
            'gemini-2.0-flash',
        ];

        $payload = [
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                    ['inline_data' => ['mime_type' => $mimeType, 'data' => $compressedBase64]],
                ]
            ]]
        ];

        $response = null;
        $lastError = '';

        foreach ($models as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            try {
                $response = Http::timeout(30)->withoutVerifying()->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($url, $payload);
                if ($response->successful()) break;
                $lastError = "[{$model}] HTTP {$response->status()}: " . substr($response->body(), 0, 200);
                Log::warning('Gemini Ripeness model failed, trying next: ' . $lastError);
                if (in_array($response->status(), [429, 503])) sleep(1);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::warning("Ripeness scan model [{$model}] connection timeout, trying next.");
                $response = null;
                $lastError = "[{$model}] connection timeout";
                continue;
            }
        }

        if ($response && $response->successful()) {
            $responseData = $response->json();
            $textOutput = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $textOutput = trim($textOutput);
            if (str_starts_with($textOutput, '```json')) {
                $textOutput = str_replace(['```json', '```'], '', $textOutput);
                $textOutput = trim($textOutput);
            }

            $data = json_decode($textOutput, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                $isProduce = (bool)($data['is_produce'] ?? false);
                $imageUrl  = asset('storage/' . $imagePath);

                auth()->user()->ripenessScans()->create([
                    'image_path'        => $imagePath,
                    'is_success'        => $isProduce,
                    'item_name'         => $data['item_name'] ?? null,
                    'ripeness_level'    => $isProduce ? ($data['ripeness_level']    ?? null) : null,
                    'ripeness_score'    => $isProduce ? ($data['ripeness_score']    ?? null) : null,
                    'color_description' => $isProduce ? ($data['color_description'] ?? null) : null,
                    'shelf_life_days'   => $isProduce ? ($data['shelf_life_days']   ?? null) : null,
                    'recommendation'    => $isProduce ? ($data['recommendation']    ?? null) : null,
                    'storage_tip'       => $isProduce ? ($data['storage_tip']       ?? null) : null,
                ]);

                return response()->json(['success' => true, 'result' => $data, 'image_url' => $imageUrl]);
            }

            auth()->user()->ripenessScans()->create(['image_path' => $imagePath, 'is_success' => false]);
            Log::error('Ripeness API JSON Parse Error: ' . $textOutput);
            return response()->json(['error' => 'Could not parse ripeness data. Please try again.'], 422);
        }

        auth()->user()->ripenessScans()->create(['image_path' => $imagePath, 'is_success' => false]);
        Log::error('Gemini Ripeness API — all models failed. Last error: ' . $lastError);
        return response()->json(['error' => 'Failed to communicate with AI. Please try again.'], 500);

        } finally {
            Cache::forget('user_' . auth()->id() . '_scanning');
        }
    }

    public function destroyRipeness(\App\Models\RipenessScan $ripenessScan)
    {
        if ($ripenessScan->user_id !== auth()->id()) abort(403);

        if ($ripenessScan->image_path) {
            $fullPath = storage_path('app/public/' . $ripenessScan->image_path);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $ripenessScan->delete();

        return back()->with('success', 'Ripeness scan deleted.');
    }
}
