<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\Restaurant\FavoriteController;
use App\Http\Controllers\Api\Restaurant\MenuController;
use App\Http\Controllers\Api\Restaurant\ReviewListController;
use App\Http\Controllers\Api\Restaurant\ShowController;
use App\Http\Controllers\Api\Restaurant\UpsertReviewController;
use App\Http\Controllers\Api\Screens\FavoritesScreenController;
use App\Http\Controllers\Api\Screens\HomeScreenController;
use App\Http\Controllers\Api\Screens\MapScreenController;
use App\Http\Controllers\Api\Screens\ProfileScreenController;
use App\Http\Controllers\Api\Screens\SearchScreenController;
use App\Http\Controllers\Api\UpdateProfileController;
use Illuminate\Support\Facades\Route;

// Health check & ping
Route::get('health', fn () => response()->json(['data' => ['status' => 'ok']]));
Route::get('ping', fn () => response()->json(['data' => ['pong' => true]]));

// Categories (public, cached)
Route::get('categories', CategoryController::class);

// Public auth routes (rate limited)
Route::middleware('throttle:auth')->group(function () {
    Route::post('register', RegisterController::class);
    Route::post('login', LoginController::class);
});

// Authenticated routes
Route::middleware(['auth:sanctum', 'not_banned'])->group(function () {
    Route::post('logout', LogoutController::class);
    Route::get('me', MeController::class);
    Route::put('me', UpdateProfileController::class);

    // User screens
    Route::prefix('screens')->group(function () {
        Route::get('home', HomeScreenController::class);
        Route::get('search', SearchScreenController::class);
        Route::get('map', MapScreenController::class);
        Route::get('favorites', FavoritesScreenController::class);
        Route::get('profile', ProfileScreenController::class);
    });

    // Restaurant details, menus, reviews, favorites
    Route::prefix('restaurants')->group(function () {
        Route::get('{id}', ShowController::class);
        Route::get('{id}/menus', MenuController::class);
        Route::get('{id}/reviews', ReviewListController::class);
        Route::put('{id}/review', [UpsertReviewController::class, 'upsert']);
        Route::delete('{id}/review', [UpsertReviewController::class, 'destroy']);
        Route::post('{id}/favorite', [FavoriteController::class, 'store']);
        Route::delete('{id}/favorite', [FavoriteController::class, 'destroy']);
    });

    // Merchant (requires merchant role)
    Route::prefix('merchant')->middleware('role:merchant')->group(function () {
        Route::get('screens/dashboard', \App\Http\Controllers\Api\Merchant\DashboardController::class);
        Route::get('store', [\App\Http\Controllers\Api\Merchant\StoreController::class, 'show']);
        Route::post('store', [\App\Http\Controllers\Api\Merchant\StoreController::class, 'store']);
        Route::put('store', [\App\Http\Controllers\Api\Merchant\StoreController::class, 'update']);
        Route::get('menus', [\App\Http\Controllers\Api\Merchant\MenuController::class, 'index']);
        Route::post('menus', [\App\Http\Controllers\Api\Merchant\MenuController::class, 'store']);
        Route::put('menus/{id}', [\App\Http\Controllers\Api\Merchant\MenuController::class, 'update']);
        Route::delete('menus/{id}', [\App\Http\Controllers\Api\Merchant\MenuController::class, 'destroy']);
        Route::get('reviews', [\App\Http\Controllers\Api\Merchant\ReviewController::class, 'index']);
    });

    // Admin (requires admin role)
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('screens/dashboard', \App\Http\Controllers\Api\Admin\DashboardController::class);
        Route::get('screens/map', \App\Http\Controllers\Api\Admin\MapController::class);
        Route::get('stores', [\App\Http\Controllers\Api\Admin\StoreController::class, 'index']);
        Route::post('stores/{id}/approve', [\App\Http\Controllers\Api\Admin\StoreController::class, 'approve']);
        Route::post('stores/{id}/ban', [\App\Http\Controllers\Api\Admin\StoreController::class, 'ban']);
        Route::delete('stores/{id}', [\App\Http\Controllers\Api\Admin\StoreController::class, 'destroy']);
        Route::get('users', [\App\Http\Controllers\Api\Admin\UserController::class, 'index']);
        Route::post('users/{id}/ban', [\App\Http\Controllers\Api\Admin\UserController::class, 'ban']);
        Route::post('users/{id}/unban', [\App\Http\Controllers\Api\Admin\UserController::class, 'unban']);
    });
});
