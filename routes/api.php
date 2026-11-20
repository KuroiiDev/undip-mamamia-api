<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// Health check & ping
Route::get('health', fn () => response()->json(['data' => ['status' => 'ok']]));
Route::get('ping', fn () => response()->json(['data' => ['pong' => true]]));

// Public auth routes (rate limited)
Route::middleware('throttle:auth')->group(function () {
    Route::post('register', RegisterController::class);
    Route::post('login', LoginController::class);
});

// Authenticated routes
Route::middleware(['auth:sanctum', 'not_banned'])->group(function () {
    Route::post('logout', LogoutController::class);
    Route::get('me', MeController::class);

    // User screens
    Route::prefix('screens')->group(function () {
        // Implemented in Fase 5
    });
    Route::prefix('restaurants')->group(function () {
        // Implemented in Fase 5
    });

    // Merchant (requires merchant role)
    Route::prefix('merchant')->middleware('role:merchant')->group(function () {
        // Implemented in Fase 7
    });

    // Admin (requires admin role)
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        // Implemented in Fase 8
    });
});
