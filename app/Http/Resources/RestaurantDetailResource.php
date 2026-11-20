<?php

namespace App\Http\Resources;

use App\Support\OpenNow;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class RestaurantDetailResource extends JsonResource
{
    public ?bool $isFavorited = null;

    public function toArray(Request $request): array
    {
        $priceLevels = [1 => 'Rp', 2 => 'RpRp', 3 => 'RpRpRp'];
        $dayLabels = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        $user = $request->user();
        $myReview = $user
            ? $this->reviews->firstWhere('user_id', $user->id)
            : null;

        $topReviews = $this->reviews
            ->where('user_id', '!=', $user?->id)
            ->sortByDesc('created_at')
            ->take(3)
            ->values();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'cover_url' => $this->cover_path
                ? Storage::disk('public')->url($this->cover_path)
                : null,
            'photos' => $this->photos->map(fn ($p) => Storage::disk('public')->url($p->path)),
            'rating_label' => number_format($this->rating_avg, 1),
            'rating_count' => $this->rating_count,
            'price_label' => $priceLevels[$this->price_level] ?? 'Rp',
            'is_open_now' => OpenNow::check($this->openingHours, $this->timezone ?? 'Asia/Jakarta'),
            'address' => $this->address,
            'phone' => $this->phone,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'categories' => $this->categories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ]),
            'opening_hours' => $this->openingHours
                ->sortBy('day_of_week')
                ->values()
                ->map(fn ($h) => [
                    'day' => $dayLabels[$h->day_of_week] ?? $h->day_of_week,
                    'open' => $h->open_time ? substr($h->open_time, 0, 5) : null,
                    'close' => $h->close_time ? substr($h->close_time, 0, 5) : null,
                    'is_closed' => $h->is_closed,
                ]),
            'is_favorited' => $this->isFavorited ?? false,
            'my_review' => $myReview ? [
                'id' => $myReview->id,
                'rating' => $myReview->rating,
                'comment' => $myReview->comment,
            ] : null,
            'top_reviews' => ReviewResource::collection($topReviews),
        ];
    }
}
