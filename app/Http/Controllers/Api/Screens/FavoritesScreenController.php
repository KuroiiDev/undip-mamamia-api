<?php

namespace App\Http\Controllers\Api\Screens;

use App\Http\Resources\RestaurantCardResource;
use App\Models\Restaurant;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class FavoritesScreenController
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $restaurants = $user->favorites()
            ->active()
            ->with('openingHours')
            ->orderByDesc('favorites.created_at')
            ->get();

        $favoritedIds = $restaurants->pluck('id')->toArray();

        $data = RestaurantCardResource::collectionWithMeta($restaurants, [], $favoritedIds);

        return ApiResponse::success($data);
    }
}
