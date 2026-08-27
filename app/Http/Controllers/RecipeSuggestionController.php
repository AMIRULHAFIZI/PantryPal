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

        // Set cross-device scanning flag so other open windows can show the overlay
        Cache::put('user_' . $userId . '_scanning', 'recipe', 180);

        try {

        $expiringStr = implode(', ', $expiringSoon);
        $otherStr = empty($otherItems) ? 'None' : implode(', ', $otherItems);

        $prompt = "You are a creative Chef AI focused on reducing food waste. You are given two lists of foods with their available quantities."
            . "\nExpiring Soon (use these first): " . $expiringStr 
            . "\nOther Pantry Items (available to support the recipe): " . $otherStr
            . "\n\nCRITICAL RULES:"
            . "\n1. Invent EXACTLY 3 distinct, tasty recipes that primarily use the 'Expiring Soon' items to prevent them from going to waste."
            . "\n2. Make each recipe meaningfully different (e.g. different cuisine style, cooking method, or meal type — breakfast, lunch, dinner, snack, etc.)."
            . "\n3. You MAY use items from 'Other Pantry Items' to complete the recipes if needed."
            . "\n4. You may assume they have basic staples like salt, pepper, oil, and water."
            . "\n5. Do NOT use any item that is not listed above. Never use items that have already expired."
            . "\n6. In each ingredients_to_use list, always include the quantity and unit for each ingredient (e.g. '500g Chicken Thighs', '2 pcs Eggs'). Use the provided quantities as your reference."
            . "\n7. If it is practically impossible to combine the expiring items into ANY sensible food recipe, return has_recipe: false with a helpful description."
            . "\n\nReturn EXACTLY a valid JSON object in this format:"
            . "\n{"
            . "\n  \"has_recipe\": true/false,"
            . "\n  \"description\": \"A short warning/reminder (only used when has_recipe is false).\","
            . "\n  \"recipes\": ["
            . "\n    {"
            . "\n      \"title\": \"Recipe Name\","
            . "\n      \"description\": \"A short enticing description of this recipe.\","
            . "\n      \"ingredients_to_use\": [\"quantity + unit + ingredient name\"],"
            . "\n      \"instructions\": [\"step 1\", \"step 2\"]"
            . "\n    }"
            . "\n  ]"
            . "\n}";

        $models = [
            'gemini-2.5-flash-lite',
            'gemini-flash-latest',
            'gemini-flash-lite-latest',
            'gemini-2.5-flash',
            'gemini-2.0-flash-lite',
            'gemini-2.0-flash',
            'gemini-3.1-flash-lite',
            'gemini-3-flash-preview',
        ];

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        $response = null;
        foreach ($models as $model) {
            $response = Http::timeout(60)->withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", $payload);
            if ($response->successful()) break;
            $status = $response->status();
            Log::warning("Recipe suggestion model [{$model}] failed HTTP {$status}, trying next.");
            // Brief pause before next attempt to avoid cascading rate-limit hits
            if (in_array($status, [429, 503])) {
                sleep(1);
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

            $jsonData = json_decode($textOutput, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                Cache::put($cacheKey, $jsonData, now()->addHours(6));
                return response()->json($jsonData);
            }
            
            Log::error('Recipe AI JSON Parse Error: ' . $textOutput);
        } else {
            Log::error('Recipe AI — all models failed. Last status: ' . ($response ? $response->status() : 'no response'));
        }

        return response()->json(['has_recipe' => false, 'error' => 'Failed to fetch from AI']);

        } finally {
            Cache::forget('user_' . $userId . '_scanning');
        }
    }
}
