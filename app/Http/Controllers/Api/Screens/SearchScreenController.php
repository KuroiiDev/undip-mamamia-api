<?php

namespace App\Http\Controllers\Api\Screens;

use App\Http\Resources\RestaurantCardResource;
use App\Models\Restaurant;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchScreenController
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $lat = $request->float('lat');
        $lng = $request->float('lng');
        $hasLocation = $request->filled('lat') && $request->filled('lng');

        $query = Restaurant::active()->with('openingHours');

        // Add distance select if location provided
        if ($hasLocation) {
            $query->select('restaurants.*')
                ->selectRaw('ST_Distance_Sphere(point(lng, lat), point(?, ?)) as distance_meters', [$lng, $lat]);
        }

        // Text search
        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($w) use ($q) {
                $w->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('address', 'LIKE', "%{$q}%");
            });
        }

        // Category filter (supports multiple)
        if ($request->filled('category')) {
            $categories = (array) $request->input('category');
            $query->whereHas('categories', function ($q) use ($categories) {
                $q->whereIn('categories.id', $categories);
            });
        }

        // Price filter
        if ($request->filled('price')) {
            $query->where('price_level', (int) $request->input('price'));
        }

        // Minimum rating filter
        if ($request->filled('rating')) {
            $query->where('rating_avg', '>=', (float) $request->input('rating'));
        }

        // Sorting & Cursor pagination
        $sort = $request->input('sort', 'rating');
        if ($sort === 'nearest' && $hasLocation) {
            $query->orderBy('distance_meters')->orderBy('id');
        } else {
            $query->orderByDesc('rating_avg')->orderBy('id');
        }

        $paginated = $query->cursorPaginate(15);

        // Get favorited IDs
        $favoritedIds = $user
            ? $user->favorites()->pluck('restaurants.id')->toArray()
            : [];

        // Build distances map
        $distances = [];
        if ($hasLocation) {
            foreach ($paginated->items() as $r) {
                $distances[$r->id] = (float) $r->distance_meters;
            }
        }

        // Filter open_now in-memory if requested
        $items = collect($paginated->items());
        if ($request->boolean('open_now')) {
            $items = $items->filter(function ($r) {
                return \App\Support\OpenNow::check($r->openingHours, $r->timezone ?? 'Asia/Jakarta');
            })->values();
        }

        $data = RestaurantCardResource::collectionWithMeta($items, $distances, $favoritedIds);

        return ApiResponse::paginated(
            $data,
            $paginated->nextCursor()?->encode()
        );
    }
}
