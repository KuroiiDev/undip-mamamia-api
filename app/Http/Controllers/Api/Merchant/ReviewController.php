<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Resources\ReviewResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ReviewController
{
    public function index(Request $request)
    {
        $restaurant = $request->user()->restaurant;

        if (! $restaurant) {
            return ApiResponse::error(
                message: 'Toko belum dibuat.',
                code: 'store_not_found',
                status: 404
            );
        }

        $reviews = $restaurant->reviews()
            ->with('user')
            ->orderByDesc('created_at')
            ->cursorPaginate(15);

        return ApiResponse::paginated(
            ReviewResource::collection($reviews),
            $reviews->nextCursor()?->encode()
        );
    }
}
