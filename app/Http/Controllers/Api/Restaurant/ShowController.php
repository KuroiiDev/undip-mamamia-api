<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Resources\RestaurantDetailResource;
use App\Models\Restaurant;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ShowController
{
    public function __invoke(Request $request, string $id)
    {
        $user = $request->user();
        $query = $user?->isAdmin() ? Restaurant::query() : Restaurant::active();

        $restaurant = $query
            ->with(['categories', 'openingHours', 'photos', 'reviews.user'])
            ->findOrFail($id);

        $resource = new RestaurantDetailResource($restaurant);
        $resource->isFavorited = $user
            ? $user->favorites()->where('restaurant_id', $id)->exists()
            : false;

        return ApiResponse::success($resource);
    }
}
