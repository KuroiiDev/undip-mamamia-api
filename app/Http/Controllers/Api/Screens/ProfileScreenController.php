<?php

namespace App\Http\Controllers\Api\Screens;

use App\Http\Resources\ReviewResource;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ProfileScreenController
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $reviews = $user->reviews()
            ->with(['restaurant:id,name,cover_path'])
            ->orderByDesc('created_at')
            ->get();

        $myReviews = $reviews->map(fn ($r) => [
            'id' => $r->id,
            'rating' => $r->rating,
            'comment' => $r->comment,
            'created_label' => $r->created_at->diffForHumans(),
            'restaurant' => [
                'id' => $r->restaurant->id,
                'name' => $r->restaurant->name,
                'cover_url' => $r->restaurant->cover_path
                    ? ApiResponse::storageUrl($r->restaurant->cover_path)
                    : null,
            ],
        ]);

        return ApiResponse::success([
            'user' => new UserResource($user),
            'review_count' => $reviews->count(),
            'my_reviews' => $myReviews,
        ]);
    }
}
