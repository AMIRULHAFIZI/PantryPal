<?php

namespace App\Http\Controllers;

use App\Models\PantryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PantryWebController extends Controller
{
    public function index() {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $items = auth()->user()->pantryItems()->orderBy('expiry_date', 'asc')->get();
        
        $totalItems = $items->count();
        $today = now()->startOfDay();
        $expired = $items->filter(function($item) use ($today) {
            if (!$item->expiry_date) return false;
            try {
                return Carbon::parse($item->expiry_date)->startOfDay()->isBefore($today);
            } catch (\Exception $e) { return false; }
        })->count();

        $expiringSoon = $items->filter(function($item) use ($today) {
            if (!$item->expiry_date) return false;
            try {
                $expiry = Carbon::parse($item->expiry_date)->startOfDay();
                return !$expiry->isBefore($today) && $today->diffInDays($expiry) <= 7;
            } catch (\Exception $e) { return false; }
        })->count();

        return view('pantry_list', compact('items', 'totalItems', 'expiringSoon', 'expired'));
    }

    public function store(Request $request) {
        $request->validate([
            'item_name'   => 'required|string|max:255',
            'quantity'    => 'required|numeric|min:0',
            'unit'        => 'nullable|string|max:20',
            'expiry_date' => 'nullable|date',
            'category'    => 'nullable|string',
        ]);

        auth()->user()->pantryItems()->create([
            'item_name'   => $request->item_name,
            'quantity'    => (float) $request->quantity,
            'unit'        => $request->unit ?? 'pcs',
            'expiry_date' => $request->expiry_date ?: null,
            'category'    => $request->category,
        ]);

        Cache::forget('recipe_suggestion_user_' . auth()->id());
        return redirect()->back()->with('success', 'Item added!');
    }

    public function edit(PantryItem $pantryItem) {
        if ($pantryItem->user_id !== auth()->id()) abort(403);
        return view('pantry_edit', compact('pantryItem'));
    }

    public function update(Request $request, PantryItem $pantryItem) {
        if ($pantryItem->user_id !== auth()->id()) abort(403);
        $request->validate([
            'item_name'   => 'required|string|max:255',
            'quantity'    => 'required|numeric|min:0',
            'unit'        => 'nullable|string|max:20',
            'expiry_date' => 'nullable|date',
            'category'    => 'nullable|string',
        ]);

        $pantryItem->update([
            'item_name'   => $request->item_name,
            'quantity'    => (float) $request->quantity,
            'unit'        => $request->unit ?? 'pcs',
            'expiry_date' => $request->expiry_date ?: null,
            'category'    => $request->category,
        ]);

        Cache::forget('recipe_suggestion_user_' . auth()->id());
        return redirect()->route('dashboard')->with('success', 'Item updated!');
    }

    public function destroy(PantryItem $pantryItem) {
        if ($pantryItem->user_id !== auth()->id()) abort(403);
        $pantryItem->delete();
        Cache::forget('recipe_suggestion_user_' . auth()->id());
        return redirect()->back()->with('success', 'Item deleted!');
    }
}