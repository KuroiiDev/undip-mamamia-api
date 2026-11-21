<?php

namespace App\Http\Controllers\Api\Screens;

use App\Http\Resources\RestaurantCardResource;
use App\Models\Category;
use App\Models\Restaurant;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeScreenController
{
    public function __invoke(Request $request)
    {
        $lat = $request->float('lat');
        $lng = $request->float('lng');
        $hasLocation = $request->filled('lat') && $request->filled('lng');
        $user = $request->user();

        // Categories (cached)
        $categories = Cache::remember('categories', 3600, function () {
            return Category::select('id', 'name', 'icon_path')
                ->orderBy('name')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'icon_url' => $c->icon_path
                        ? ApiResponse::storageUrl($c->icon_path)
                        : null,
                ])
                ->values()
                ->all();
        });

        // Favorited IDs for the current user
        $favoritedIds = $user
            ? $user->favorites()->pluck('restaurants.id')->toArray()
            : [];

        // Nearby restaurants (sorted by distance)
        $nearby = collect();
        $nearbyDistances = [];
        if ($hasLocation) {
            $nearbyQuery = Restaurant::active()
                ->with('openingHours')
                ->select('restaurants.*')
                ->selectRaw('ST_Distance_Sphere(point(lng, lat), point(?, ?)) as distance_meters', [$lng, $lat])
                ->orderBy('distance_meters')
                ->limit(10);

            $nearby = $nearbyQuery->get();
            foreach ($nearby as $r) {
                $nearbyDistances[$r->id] = (float) $r->distance_meters;
            }
        }

        // Top rated restaurants
        $topRated = Restaurant::active()
            ->with('openingHours')
            ->where('rating_count', '>', 0)
            ->orderByDesc('rating_avg')
            ->limit(10)
            ->get();

        $topRatedDistances = [];
        if ($hasLocation && $topRated->isNotEmpty()) {
            foreach ($topRated as $r) {
                $topRatedDistances[$r->id] = $this->haversineDistance($lat, $lng, $r->lat, $r->lng);
            }
        }

        return ApiResponse::success([
            'categories' => $categories,
            'nearby' => RestaurantCardResource::collectionWithMeta($nearby, $nearbyDistances, $favoritedIds)->values()->all(),
            'top_rated' => RestaurantCardResource::collectionWithMeta($topRated, $topRatedDistances, $favoritedIds)->values()->all(),
        ]);
    }

    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
