<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PantryController; 

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/items', [PantryController::class, 'index']);
    Route::post('/items', [PantryController::class, 'store']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
