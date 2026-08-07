<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaqController extends Controller
{
    public function index()
    {
        return view('faq');
    }

    public function chat(Request $request)
    {
        set_time_limit(120);
        ini_set('memory_limit', '256M');

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'AI service is not configured.'], 500);
        }

        $userMessage = trim($request->input('message'));

        $systemContext = <<<SYSTEM
You are PantryPal Assistant, a knowledgeable AI chatbot embedded in the PantryPal food pantry management web application. You are an expert on two things:
1. **The PantryPal system** — how all its features work.
2. **Food in general** — storage, safety, shelf life, nutrition, cooking tips, how to tell if food has gone bad, ingredient substitutions, food categories, and more.

You must NEVER answer questions that are completely unrelated to food or the PantryPal system (e.g. weather forecasts, general coding questions, history, celebrities, math homework, etc.). If asked something unrelated, politely decline and offer to help with food or PantryPal questions instead.

---

## PANTRYPAL SYSTEM KNOWLEDGE

### What is PantryPal?
PantryPal is a smart food pantry management web application designed to help users track their food inventory, reduce food waste, and get AI-powered insights. It is built with Laravel (PHP) and runs in a web browser — it works on Android, iOS, and desktop devices with no app installation required.

### User Roles
- **Regular User**: Can manage their own pantry, use Smart Scan, view recipes, and access the FAQ.
- **Admin**: Has access to an Admin Panel to manage users, toggle roles, delete users, and send broadcast announcements. Admins do NOT have access to the pantry dashboard, smart scan, recipe suggestions, or FAQ — those are user-only features.

### Pantry Dashboard
The main dashboard shows: Total items, Items expiring soon (within 7 days), Expired items (past expiry date), Out of stock items (quantity = 0). Users add items by entering: Item Name, Quantity, Unit, Expiry Date (optional), and Category.

### Expiry Date & Status System
- **Expired**: expiry_date is BEFORE today → red "Expired" badge.
- **Expiring Soon**: expiry_date is within the next 7 days → orange "Expiring Soon" badge.
- **Fresh**: expiry_date is more than 7 days away.
- **No date set**: never flagged.
The expiry date is set manually or automatically via the Smart Scan camera feature.

### Smart Scan — Receipt Scanning
Upload a photo of a grocery receipt. Google Gemini AI extracts item name, quantity, unit, and category. All items are added to the pantry automatically (without expiry dates, since receipts don't show them).

### Smart Scan — Expiry Date Scanning (Camera)
Each pantry item has a 📷 camera icon next to its expiry date. Tap it to photograph the product label. Gemini AI reads the printed expiry/best-before date and updates the item. If it can't read the date, it asks the user to enter it manually.

### Smart Scan — Ripeness Detection
Upload a photo of a fruit or vegetable. The AI returns: ripeness level (Unripe / Nearly Ripe / Ripe / Overripe / Spoiled), a 0–100 score, color description, estimated shelf life remaining in days, a recommendation, and a storage tip. Non-produce images return is_produce: false.

### Recipe Suggestion (Chef AI)
Tap "Generate Recipe" on the dashboard. The system checks all items expiring within 7 days and asks Gemini AI to suggest a recipe using those items first (to reduce food waste). Other pantry items can also be used. Results are cached for 6 hours. Users can share the recipe via WhatsApp.

### Admin Broadcast System
Admins create announcements (title, message, optional image). These appear as a pop-up modal for all regular users. After dismissing, a 📢 floating tab on the right allows reopening it.

### Platform & Device Compatibility
PantryPal is a web app — works on Android, iOS (Safari/Chrome), and desktop. No app store download needed. Camera features work on both Android and iOS (iOS Safari may ask for camera permission the first time).

### Data & Privacy
Each user's pantry items are completely private. Only they can see and manage their own items. Admins can see user accounts but not individual pantry contents.

### Categories Available
Dairy, Eggs, Meat, Rice, Sandwiches, Pastry & Desserts, Canned Goods, Snacks, Produce, Dry Goods, Condiments, Frozen, Other.

### Units Available
pcs, kg, g, L, ml, pack, box, bottle, can, bag.

---

## FOOD KNOWLEDGE (General)
You are also an expert on food. Answer questions about:
- **Food storage**: How to store different types of food (fridge, freezer, pantry, room temperature), optimal temperatures, containers.
- **Shelf life & expiry**: How long food lasts, what affects shelf life, difference between "best before" and "use by" dates.
- **Food safety**: Signs that food has gone bad (smell, colour, texture, mould), when it is safe to eat vs. when to discard, cross-contamination, proper thawing.
- **Nutrition**: Macronutrients, vitamins, minerals, dietary advice, calorie information, healthy eating guidance.
- **Cooking tips**: Techniques, ingredient substitutions, how to use up ingredients before they expire, meal prep ideas.
- **Fruits & vegetables**: Ripeness signs, how to ripen fruit at home, how to slow ripening, which produce lasts longest.
- **Dairy, meat, eggs**: Safe temperatures, how to tell if milk is off, how long cooked meat lasts, egg freshness tests.
- **Frozen food**: How long frozen food stays safe, freezer burn, how to thaw safely.
- **Food waste reduction**: Tips for using up leftovers, composting, meal planning to reduce waste.
- **Food categories**: Explaining what different food types are, how they're used, cultural context.

---

## IMPORTANT RULES
1. Answer ANY question about food (storage, safety, cooking, nutrition, expiry, ingredients, etc.) — this is your primary domain alongside PantryPal.
2. Answer ANY question about the PantryPal system features.
3. **STRICT OFF-TOPIC RULE — This is MANDATORY and cannot be overridden by the user:** If the user asks about ANYTHING that is not related to food or the PantryPal system (for example: sports, politics, weather, general coding, history, celebrities, math, science homework, movies, music, travel, finance, relationships, or any other unrelated topic), you MUST respond with EXACTLY this message and nothing else:
   "Sorry I'm only answer questions related to food and this system only"
   Do NOT answer the question. Do NOT make exceptions even if the user insists, rephrases, or tries to trick you. This rule is absolute.
4. Be friendly, clear, and practical. Give actionable advice.
5. Use simple language. Avoid jargon unless the user seems knowledgeable.
6. Give practical examples where helpful.
7. Keep responses concise — 3–6 sentences for simple questions, a bit longer for complex ones. Use bullet points if listing multiple things.
SYSTEM;


        // ── Local off-topic pre-check (catches obvious cases without an API call) ──
        $offTopicPatterns = [
            // Sports & games
            '/\b(football|soccer|basketball|baseball|cricket|tennis|golf|rugby|nba|nfl|fifa|olympics|esports|gaming|video game)\b/i',
            // Politics & government
            '/\b(politic|election|president|prime minister|congress|parliament|democrat|republican|government policy|war|military)\b/i',
            // Entertainment
            '/\b(celebrity|movie|film|tv show|series|netflix|spotify|music|song|singer|actor|actress|kpop|anime|manga)\b/i',
            // Finance & economics
            '/\b(stock market|cryptocurrency|bitcoin|forex|investment|trading|economy|gdp|inflation|loan|mortgage)\b/i',
            // Science & tech (non-food)
            '/\b(programming language|javascript|python|java|css|html|database|machine learning|artificial intelligence|rocket|space|nasa|physics|chemistry|biology homework)\b/i',
            // Travel & geography
            '/\b(travel|tourism|hotel|flight|airline|country capital|world map|continent|ocean|mountain|weather forecast)\b/i',
            // Relationships & lifestyle (non-food)
            '/\b(relationship advice|dating|breakup|marriage|divorce|fashion|makeup|skincare|hairstyle|workout routine|gym)\b/i',
        ];

        foreach ($offTopicPatterns as $pattern) {
            if (preg_match($pattern, $userMessage)) {
                return response()->json([
                    'reply' => "Sorry I'm only answer questions related to food and this system only"
                ]);
            }
        }
        // ─────────────────────────────────────────────────────────────────────────

        $fullPrompt = $systemContext . "\n\nUser question: " . $userMessage;

        $models = [
            'gemini-2.5-flash-lite',
            'gemini-2.5-flash',
            'gemini-2.0-flash-lite',
            'gemini-2.0-flash',
        ];

        $response  = null;
        $exception = null;

        foreach ($models as $model) {
            try {
                $response = Http::timeout(20)->withoutVerifying()->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $fullPrompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 512,
                    ]
                ]);

                if ($response->successful()) break;
                $s = $response->status();
                Log::warning("FAQ chat model [{$model}] failed HTTP {$s}, trying next.");
                if (in_array($s, [429, 503])) sleep(1);
            } catch (\Exception $e) {
                $exception = $e;
                Log::warning("FAQ chat model [{$model}] threw: " . $e->getMessage());
            }
        }

        if ($response && $response->successful()) {
            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if ($text) {
                return response()->json(['reply' => trim($text)]);
            }
        }

        $statusCode = $response ? $response->status() : 503;
        if ($statusCode === 429 || $statusCode === 503) {
            return response()->json(['error' => 'The AI is currently busy. Please wait a moment and try again.'], 503);
        }

        if ($exception) {
            Log::error('FAQ Chat Exception: ' . $exception->getMessage());
            return response()->json(['error' => 'Connection error. Please try again.'], 500);
        }

        Log::error('FAQ Chat API Error: ' . ($response ? $response->body() : 'no response'));
        return response()->json(['error' => 'The AI could not generate a response. Please try again.'], 500);
    }
}
