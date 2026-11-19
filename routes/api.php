<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Note: health check & ping
Route::get('health', fn () => response()->json(['data' => ['status' => 'ok']]));
Route::get('ping', fn () => response()->json(['data' => ['pong' => true]]));

// Note: public routes (no login)
Route::middleware('throttle:auth')->group(function () {
    // TODO: implement Login, Register logic
});

// Note: routes that require login (all roles)
Route::middleware('auth:sanctum')->group(function () {

    // Note: user: home, map, detail, review, favorite, profile
    Route::prefix('screens')->group(function () {
        // TODO: implement screens api per-screen
    });
    Route::prefix('restaurants')->group(function () {
        // TODO: implement restaurants data api
    });

    // Note: merchant: dashboard, shop, menu, incoming reviews
    Route::prefix('merchant')->group(function () {
        // TODO: implement merchant api + middleware role:merchant
    });

    // Admin
    Route::prefix('admin')->group(function () {
        // TODO: implement admin api + middleware role:admin
    });
});
