<?php

namespace App\Observers;

use App\Models\Restaurant;
use App\Models\Review;

class ReviewObserver
{
    public function saved(Review $review): void
    {
        $this->recalculateRestaurantRating($review->restaurant_id);
    }

    public function deleted(Review $review): void
    {
        $this->recalculateRestaurantRating($review->restaurant_id);
    }

    public function restored(Review $review): void
    {
        $this->recalculateRestaurantRating($review->restaurant_id);
    }

    public function forceDeleted(Review $review): void
    {
        $this->recalculateRestaurantRating($review->restaurant_id);
    }

    protected function recalculateRestaurantRating(string $restaurantId): void
    {
        $stats = Review::where('restaurant_id', $restaurantId)
            ->selectRaw('COUNT(*) as total_count, AVG(rating) as avg_rating')
            ->first();

        $restaurant = Restaurant::find($restaurantId);
        if ($restaurant) {
            $restaurant->updateQuietly([
                'rating_count' => (int) ($stats->total_count ?? 0),
                'rating_avg' => round((float) ($stats->avg_rating ?? 0), 2),
            ]);
        }
    }
}
