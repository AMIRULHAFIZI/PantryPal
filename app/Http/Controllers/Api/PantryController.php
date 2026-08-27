<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PantryItem; // Check if this line is missing or wrong!

class PantryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $items = PantryItem::where('user_id', $request->user()->id)->get();
        return response()->json($items, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string',
            'quantity' => 'required|integer',
            'expiry_date' => 'required|date',
            'ripeness_info' => 'nullable|string',
            'category' => 'nullable|string',
        ]);

        $item = PantryItem::create($validated);

        return response()->json([
            'message' => 'Item added to PantryPal!',
            'data' => $item
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
