<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Resources\ReviewResource;
use App\Models\Restaurant;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ReviewListController
{
    public function __invoke(Request $request, string $id)
    {
        $query = $request->user()?->isAdmin() ? Restaurant::query() : Restaurant::active();
        $restaurant = $query->findOrFail($id);

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
