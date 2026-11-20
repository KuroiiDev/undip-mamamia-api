<?php

namespace App\Http\Controllers\Api\Screens;

use App\Models\Restaurant;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class MapScreenController
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'nullable|numeric|min:100|max:20000',
        ]);

        $lat = $request->float('lat');
        $lng = $request->float('lng');
        $radius = $request->float('radius', 3000);

        // Calculate bounding box
        $latDelta = $radius / 111320; // roughly 1 degree lat ≈ 111.32 km
        $lngDelta = $radius / (111320 * cos(deg2rad($lat)));

        $restaurants = Restaurant::active()
            ->select('id', 'name', 'lat', 'lng', 'rating_avg')
            ->whereBetween('lat', [$lat - $latDelta, $lat + $latDelta])
            ->whereBetween('lng', [$lng - $lngDelta, $lng + $lngDelta])
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'lat' => $r->lat,
                'lng' => $r->lng,
                'rating_label' => number_format($r->rating_avg, 1),
            ]);

        return ApiResponse::success($restaurants);
    }
}
