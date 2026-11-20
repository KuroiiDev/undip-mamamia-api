<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Resources\ReviewResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class DashboardController
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $restaurant = $user->restaurant;

        if (! $restaurant) {
            return ApiResponse::success([
                'store' => null,
            ]);
        }

        $latestReviews = $restaurant->reviews()
            ->with('user')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return ApiResponse::success([
            'store' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'status' => $restaurant->status,
                'status_reason' => $restaurant->status_reason,
                'rating_label' => number_format($restaurant->rating_avg, 1),
                'review_count' => $restaurant->rating_count,
                'menu_count' => $restaurant->menuItems()->count(),
            ],
            'latest_reviews' => ReviewResource::collection($latestReviews),
        ]);
    }
}
