<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RecipeSuggestionController extends Controller
{
    public function suggest()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = $user->id;
        $cacheKey = "recipe_suggestion_user_{$userId}";

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $items = $user->pantryItems()->get();
        if ($items->isEmpty()) {
            return response()->json(['has_recipe' => false, 'no_expiring_items' => true]);
        }

        $today = now()->startOfDay();
        
        $expiringSoon = [];
        $otherItems = [];
        $expiredCount = 0;

        foreach ($items as $item) {
            try {
                if (!$item->expiry_date) {
                    $qty = rtrim(rtrim(number_format((float)$item->quantity, 3), '0'), '.');
                    $unit = $item->unit ?? 'pcs';
                    $otherItems[] = "{$item->item_name} ({$qty} {$unit})";
                    continue;
                }

                $expiry = Carbon::parse($item->expiry_date)->startOfDay();

                if ($expiry->isBefore($today)) {
                    $expiredCount++;
                    continue;
                }

                $qty = rtrim(rtrim(number_format((float)$item->quantity, 3), '0'), '.');
                $unit = $item->unit ?? 'pcs';
                $label = "{$item->item_name} ({$qty} {$unit})";

                $daysUntilExpiry = $today->diffInDays($expiry, false);

                if ($daysUntilExpiry <= 7) {
                    $expiringSoon[] = $label;
                } else {
                    $otherItems[] = $label;
                }
            } catch (\Exception $e) {}
        }

        if (empty($expiringSoon)) {
            if ($expiredCount > 0) {
                $data = [
                    'has_recipe'   => false,
                    'no_expiring_items' => false,
                    'description'  => "You have {$expiredCount} expired item(s) in your pantry that should be discarded. None of your other items are expiring soon, so no recipe is needed right now — but please clear out those expired items!",
                ];
                Cache::put($cacheKey, $data, now()->addHours(1));
                return response()->json($data);
            }
            $data = ['has_recipe' => false, 'no_expiring_items' => true];
            Cache::put($cacheKey, $data, now()->addHours(6));
            return response()->json($data);
        }

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'Gemini API not configured'], 500);
        }

        $expiringStr = implode(', ', $expiringSoon);
        $otherStr = empty($otherItems) ? 'None' : implode(', ', $otherItems);

        $prompt = "You are a creative Chef AI focused on reducing food waste. You are given two lists of foods with their available quantities."
            . "\nExpiring Soon (use these first): " . $expiringStr 
            . "\nOther Pantry Items (available to support the recipe): " . $otherStr
            . "\n\nCRITICAL RULES:"
            . "\n1. Try to invent a tasty recipe that primarily uses the 'Expiring Soon' items to prevent them from going to waste."
            . "\n2. You MAY use items from 'Other Pantry Items' to complete the recipe if needed."
            . "\n3. You may assume they have basic staples like salt, pepper, oil, and water."
            . "\n4. Do NOT use any item that is not listed above. Never use items that have already expired."
            . "\n5. In the ingredients_to_use list, always include the quantity and unit for each ingredient (e.g. '500g Chicken Thighs', '2 pcs Eggs', '200g Shiitake Mushrooms'). Use the provided quantities as your reference."
            . "\n6. If it is practically impossible to combine the expiring items into a sensible food recipe (for example, if the only expiring item is 'Soda' or an unrelated mismatch that cannot form a meal/snack), DO NOT force a recipe."
            . "\n7. If a recipe is impossible, return has_recipe: false and a helpful reminder description."
            . "\n\nReturn EXACTLY a valid JSON object in this format:"
            . "\n{"
            . "\n  \"has_recipe\": true/false,"
            . "\n  \"title\": \"Recipe Name (if applicable)\","
            . "\n  \"description\": \"A short enticing description, or a warning if no recipe applies.\","
            . "\n  \"ingredients_to_use\": [\"quantity + unit + ingredient name\", \"e.g. 500g Chicken Thighs\"],"
            . "\n  \"instructions\": [\"step 1\", \"step 2\"]"
            . "\n}";

        $response = Http::timeout(60)->withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
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

            $jsonData = json_decode($textOutput, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                Cache::put($cacheKey, $jsonData, now()->addHours(6));
                return response()->json($jsonData);
            }
            
            Log::error('Recipe AI JSON Parse Error: ' . $textOutput);
        }

        return response()->json(['has_recipe' => false, 'error' => 'Failed to fetch from AI']);
    }
}
