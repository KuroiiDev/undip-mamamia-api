<?php

namespace App\Http\Resources;

use App\Support\DistanceFormatter;
use App\Support\OpenNow;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class RestaurantCardResource extends JsonResource
{
    /**
     * Distance in meters (injected from controller).
     */
    public ?float $distanceMeters = null;

    /**
     * Whether to include the is_favorited field.
     */
    public ?bool $isFavorited = null;

    public function toArray(Request $request): array
    {
        $priceLevels = [1 => 'Rp', 2 => 'RpRp', 3 => 'RpRpRp'];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'cover_url' => $this->cover_path
                ? Storage::disk('public')->url($this->cover_path)
                : null,
            'rating_label' => number_format($this->rating_avg, 1),
            'rating_count' => $this->rating_count,
            'price_label' => $priceLevels[$this->price_level] ?? 'Rp',
            'distance_label' => DistanceFormatter::format($this->distanceMeters),
            'is_open_now' => OpenNow::check(
                $this->relationLoaded('openingHours') ? $this->openingHours : collect(),
                $this->timezone ?? 'Asia/Jakarta'
            ),
            'is_favorited' => $this->isFavorited ?? false,
        ];
    }

    /**
     * Create a collection with distances and favorites injected.
     *
     * @param  \Illuminate\Support\Collection  $restaurants
     * @param  array<string, float>  $distances  Map of restaurant ID → distance in meters
     * @param  array<string>  $favoritedIds  List of favorited restaurant IDs
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public static function collectionWithMeta($restaurants, array $distances = [], array $favoritedIds = [])
    {
        return $restaurants->map(function ($restaurant) use ($distances, $favoritedIds) {
            $resource = new static($restaurant);
            $resource->distanceMeters = $distances[$restaurant->id] ?? null;
            $resource->isFavorited = in_array($restaurant->id, $favoritedIds);

            return $resource;
        });
    }
}
