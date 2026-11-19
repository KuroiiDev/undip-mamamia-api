<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Restaurant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_creation_and_deletion_updates_restaurant_rating_stats(): void
    {
        $owner = User::factory()->create([
            'role' => 'merchant',
            'status' => 'active',
        ]);

        $restaurant = Restaurant::create([
            'owner_id' => $owner->id,
            'name' => 'Resto Uji Coba',
            'address' => 'Jl. Uji No. 1',
            'lat' => -6.99,
            'lng' => 110.42,
            'price_level' => 1,
            'status' => 'active',
        ]);

        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        // 1. Initial rating
        $this->assertEquals(0.00, $restaurant->rating_avg);
        $this->assertEquals(0, $restaurant->rating_count);

        // 2. Add review from user 1 (rating 5)
        $review1 = Review::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user1->id,
            'rating' => 5,
            'comment' => 'Sangat enak!',
        ]);

        $restaurant->refresh();
        $this->assertEquals(5.00, $restaurant->rating_avg);
        $this->assertEquals(1, $restaurant->rating_count);

        // 3. Add review from user 2 (rating 3)
        $review2 = Review::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user2->id,
            'rating' => 3,
            'comment' => 'Biasa saja.',
        ]);

        $restaurant->refresh();
        $this->assertEquals(4.00, $restaurant->rating_avg); // (5+3)/2 = 4.00
        $this->assertEquals(2, $restaurant->rating_count);

        // 4. Delete review 2
        $review2->delete();

        $restaurant->refresh();
        $this->assertEquals(5.00, $restaurant->rating_avg);
        $this->assertEquals(1, $restaurant->rating_count);
    }
}
