<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'in:pending,active,banned'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'radius' => ['nullable', 'numeric', 'min:100', 'max:50000'],
        ]);

        $query = Restaurant::with('owner:id,name,email');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('lat') && $request->filled('lng')) {
            $lat = $request->float('lat');
            $lng = $request->float('lng');
            $radius = $request->float('radius', 10000);

            $latDelta = $radius / 111320;
            $lngDelta = $radius / (111320 * max(cos(deg2rad($lat)), 0.0001));

            $query->whereBetween('lat', [$lat - $latDelta, $lat + $latDelta])
                ->whereBetween('lng', [$lng - $lngDelta, $lng + $lngDelta]);
        }

        $restaurants = $query->get()
            ->map(fn (Restaurant $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'status' => $r->status,
                'status_reason' => $r->status_reason,
                'address' => $r->address,
                'phone' => $r->phone,
                'lat' => (float) $r->lat,
                'lng' => (float) $r->lng,
                'rating_avg' => (float) $r->rating_avg,
                'rating_count' => (int) $r->rating_count,
                'rating_label' => number_format((float) $r->rating_avg, 1),
                'cover_url' => ApiResponse::storageUrl($r->cover_path),
                'owner' => $r->owner ? [
                    'id' => $r->owner->id,
                    'name' => $r->owner->name,
                    'email' => $r->owner->email,
                ] : null,
            ])
            ->values()
            ->all();

        return ApiResponse::success($restaurants);
    }
}
