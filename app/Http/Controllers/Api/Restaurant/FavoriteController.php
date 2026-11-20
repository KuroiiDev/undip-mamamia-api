<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Models\Restaurant;
use Illuminate\Http\Request;

class FavoriteController
{
    /**
     * Add a restaurant to favorites (POST).
     */
    public function store(Request $request, string $id)
    {
        $restaurant = Restaurant::active()->findOrFail($id);
        $user = $request->user();

        $user->favorites()->syncWithoutDetaching([$restaurant->id]);

        return response()->noContent();
    }

    /**
     * Remove a restaurant from favorites (DELETE).
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $user->favorites()->detach($id);

        return response()->noContent();
    }
}
