<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PantryWebController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [PantryWebController::class, 'index'])->name('dashboard');
    Route::post('/store', [PantryWebController::class, 'store'])->name('pantry.store');
    Route::get('/pantry/{pantryItem}/edit', [PantryWebController::class, 'edit'])->name('pantry.edit');
    Route::put('/pantry/{pantryItem}', [PantryWebController::class, 'update'])->name('pantry.update');
    Route::delete('/pantry/{pantryItem}', [PantryWebController::class, 'destroy'])->name('pantry.destroy');

    // Smart Scan Routes
    Route::get('/smart-scan', [App\Http\Controllers\SmartScanController::class, 'index'])->name('smart-scan.index');
    Route::post('/smart-scan/receipt', [App\Http\Controllers\SmartScanController::class, 'uploadReceipt'])->name('smart-scan.upload');
    Route::post('/pantry/{pantryItem}/scan-expiry', [App\Http\Controllers\SmartScanController::class, 'updateItemExpiry'])->name('pantry.scan-expiry');

    // AI Recipe Suggestion
    Route::get('/recipe-suggestion', [App\Http\Controllers\RecipeSuggestionController::class, 'suggest'])->name('recipe.suggestion');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/toggle-role', [AdminController::class, 'toggleRole'])->name('users.toggle-role');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');

    // Broadcast / Ads routes
    Route::get('/broadcasts', [AdminController::class, 'broadcasts'])->name('broadcasts');
    Route::post('/broadcasts', [AdminController::class, 'storeBroadcast'])->name('broadcasts.store');
    Route::post('/broadcasts/{broadcast}/toggle', [AdminController::class, 'toggleBroadcast'])->name('broadcasts.toggle');
    Route::delete('/broadcasts/{broadcast}', [AdminController::class, 'deleteBroadcast'])->name('broadcasts.destroy');
});
