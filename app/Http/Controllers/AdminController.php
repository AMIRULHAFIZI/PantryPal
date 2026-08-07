<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AdminBroadcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalUsers      = User::count();
        $totalItems      = \App\Models\PantryItem::count();
        $totalScans      = \App\Models\ReceiptScan::count();
        $totalAdmins     = User::where('role', 'admin')->count();
        $recentUsers     = User::latest()->take(5)->get();
        $totalBroadcasts = AdminBroadcast::count();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalItems', 'totalScans', 'totalAdmins', 'recentUsers', 'totalBroadcasts'
        ));
    }

    public function users()
    {
        $users = User::withCount('pantryItems')->latest()->get();
        return view('admin.users', compact('users'));
    }

    public function toggleRole(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')
                ->with('error', 'You cannot change your own role.');
        }

        $user->update([
            'role' => $user->role === 'admin' ? 'user' : 'admin',
        ]);

        $newRole = ucfirst($user->role);
        return redirect()->route('admin.users')
            ->with('success', "{$user->name} is now a {$newRole}.");
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')
                ->with('error', 'You cannot delete your own account here.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', "User \"{$name}\" and all their data have been deleted.");
    }


    public function broadcasts()
    {
        $broadcasts = AdminBroadcast::latest()->get();
        return view('admin.broadcasts', compact('broadcasts'));
    }

    public function storeBroadcast(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'message'    => 'required|string',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('broadcasts', 'public');
        }

        AdminBroadcast::create([
            'title'      => $request->title,
            'message'    => $request->message,
            'image_path' => $imagePath,
            'is_active'  => $request->boolean('is_active', true),
            'expires_at' => $request->expires_at ?: null,
        ]);

        return redirect()->route('admin.broadcasts')
            ->with('success', 'Broadcast sent successfully!');
    }

    public function toggleBroadcast(AdminBroadcast $broadcast)
    {
        $broadcast->update(['is_active' => !$broadcast->is_active]);

        $status = $broadcast->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.broadcasts')
            ->with('success', "Broadcast \"{$broadcast->title}\" has been {$status}.");
    }

    public function deleteBroadcast(AdminBroadcast $broadcast)
    {
        if ($broadcast->image_path) {
            Storage::disk('public')->delete($broadcast->image_path);
        }

        $title = $broadcast->title;
        $broadcast->delete();

        return redirect()->route('admin.broadcasts')
            ->with('success', "Broadcast \"{$title}\" has been deleted.");
    }
}
