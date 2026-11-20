<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Requests\Restaurant\UpsertReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Restaurant;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class UpsertReviewController
{
    /**
     * Create or update a review (PUT).
     */
    public function upsert(UpsertReviewRequest $request, string $id)
    {
        $restaurant = Restaurant::active()->findOrFail($id);
        $user = $request->user();

        $review = $restaurant->reviews()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'rating' => $request->validated('rating'),
                'comment' => $request->validated('comment'),
            ]
        );

        $review->load('user');

        return ApiResponse::success(new ReviewResource($review));
    }

    /**
     * Delete the user's own review (DELETE).
     */
    public function destroy(Request $request, string $id)
    {
        $restaurant = Restaurant::active()->findOrFail($id);
        $user = $request->user();

        $review = $restaurant->reviews()
            ->where('user_id', $user->id)
            ->first();

        if ($review) {
            $review->delete();
        }

        return response()->noContent();
    }
}
