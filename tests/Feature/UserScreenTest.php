<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\OpeningHour;
use App\Models\Restaurant;
use App\Models\Review;
use App\Models\User;
use App\Support\DistanceFormatter;
use App\Support\OpenNow;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_distance_formatter(): void
    {
        $this->assertNull(DistanceFormatter::format(null));
        $this->assertEquals('650 m', DistanceFormatter::format(650));
        $this->assertEquals('999 m', DistanceFormatter::format(999));
        $this->assertEquals('1.0 km', DistanceFormatter::format(1000));
        $this->assertEquals('1.2 km', DistanceFormatter::format(1240));
        $this->assertEquals('15.5 km', DistanceFormatter::format(15500));
    }

    public function test_open_now_logic(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-20 14:00:00', 'Asia/Jakarta')); // Friday, 14:00

        $hours = collect([
            new OpeningHour([
                'day_of_week' => 5, // Friday
                'open_time' => '09:00:00',
                'close_time' => '22:00:00',
                'is_closed' => false,
            ]),
        ]);

        $this->assertTrue(OpenNow::check($hours, 'Asia/Jakarta'));

        // Closed day
        $closedHours = collect([
            new OpeningHour([
                'day_of_week' => 5,
                'open_time' => '09:00:00',
                'close_time' => '22:00:00',
                'is_closed' => true,
            ]),
        ]);
        $this->assertFalse(OpenNow::check($closedHours, 'Asia/Jakarta'));

        // Past closing time
        Carbon::setTestNow(Carbon::parse('2026-11-20 23:00:00', 'Asia/Jakarta'));
        $this->assertFalse(OpenNow::check($hours, 'Asia/Jakarta'));

        Carbon::setTestNow(null);
    }

    public function test_categories_endpoint_cached(): void
    {
        Category::factory()->create(['name' => 'Kopi']);
        Category::factory()->create(['name' => 'Bakso']);

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'icon_url'],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_home_screen_without_location(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Restaurant::factory()->create([
            'status' => 'active',
            'rating_avg' => 4.5,
            'rating_count' => 10,
        ]);

        $response = $this->getJson('/api/v1/screens/home');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'categories',
                    'nearby',
                    'top_rated' => [
                        '*' => [
                            'id',
                            'name',
                            'cover_url',
                            'rating_label',
                            'rating_count',
                            'price_label',
                            'distance_label',
                            'is_open_now',
                            'is_favorited',
                        ],
                    ],
                ],
            ]);

        $this->assertEmpty($response->json('data.nearby'));
        $this->assertNotEmpty($response->json('data.top_rated'));
        $this->assertNull($response->json('data.top_rated.0.distance_label'));
    }

    public function test_home_screen_with_location(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $restaurant = Restaurant::factory()->create([
            'status' => 'active',
            'lat' => -6.9900000,
            'lng' => 110.4200000,
            'rating_avg' => 4.8,
            'rating_count' => 20,
        ]);

        $response = $this->getJson('/api/v1/screens/home?lat=-6.9900000&lng=110.4200000');

        $response->assertOk();
        $this->assertNotEmpty($response->json('data.nearby'));
        $this->assertNotNull($response->json('data.nearby.0.distance_label'));
        $this->assertNotNull($response->json('data.top_rated.0.distance_label'));
    }

    public function test_search_screen_filters(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $cat = Category::factory()->create(['name' => 'Dessert']);

        $r1 = Restaurant::factory()->create([
            'name' => 'Gelato Paradise',
            'status' => 'active',
            'price_level' => 2,
            'rating_avg' => 4.7,
        ]);
        $r1->categories()->attach($cat->id);

        $r2 = Restaurant::factory()->create([
            'name' => 'Warung Nasi',
            'status' => 'active',
            'price_level' => 1,
            'rating_avg' => 3.5,
        ]);

        // Search by query
        $res = $this->getJson('/api/v1/screens/search?q=Gelato');
        $res->assertOk();
        $this->assertCount(1, $res->json('data'));
        $this->assertEquals($r1->id, $res->json('data.0.id'));

        // Search by category
        $resCat = $this->getJson("/api/v1/screens/search?category={$cat->id}");
        $resCat->assertOk();
        $this->assertCount(1, $resCat->json('data'));

        // Search by min rating
        $resRating = $this->getJson('/api/v1/screens/search?rating=4.0');
        $resRating->assertOk();
        $this->assertCount(1, $resRating->json('data'));
    }

    public function test_search_screen_cursor_pagination(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Restaurant::factory()->count(20)->create(['status' => 'active']);

        $resPage1 = $this->getJson('/api/v1/screens/search');
        $resPage1->assertOk()
            ->assertJsonCount(15, 'data');
        $nextCursor = $resPage1->json('meta.next_cursor');
        $this->assertNotNull($nextCursor);

        $resPage2 = $this->getJson('/api/v1/screens/search?cursor='.$nextCursor);
        $resPage2->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_map_screen_bounding_box_and_validation(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Validation fails if no lat/lng
        $this->getJson('/api/v1/screens/map')->assertStatus(422);

        $rNear = Restaurant::factory()->create([
            'name' => 'Near Cafe',
            'status' => 'active',
            'lat' => -6.9900000,
            'lng' => 110.4200000,
        ]);

        $rFar = Restaurant::factory()->create([
            'name' => 'Far Away Cafe',
            'status' => 'active',
            'lat' => -7.5000000,
            'lng' => 110.4200000,
        ]);

        $res = $this->getJson('/api/v1/screens/map?lat=-6.9900000&lng=110.4200000&radius=3000');
        $res->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'lat', 'lng', 'rating_label'],
                ],
            ]);

        $ids = collect($res->json('data'))->pluck('id')->toArray();
        $this->assertContains($rNear->id, $ids);
        $this->assertNotContains($rFar->id, $ids);
    }

    public function test_restaurant_detail(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $cat = Category::factory()->create(['name' => 'Kafe']);
        $restaurant = Restaurant::factory()->create([
            'name' => 'Test Resto',
            'status' => 'active',
            'price_level' => 2,
        ]);
        $restaurant->categories()->attach($cat->id);

        OpeningHour::factory()->create([
            'restaurant_id' => $restaurant->id,
            'day_of_week' => 1,
            'open_time' => '08:00:00',
            'close_time' => '21:00:00',
            'is_closed' => false,
        ]);

        Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'My awesome review',
        ]);

        $otherUser = User::factory()->create(['name' => 'Other User']);
        Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $otherUser->id,
            'rating' => 4,
            'comment' => 'Other review',
        ]);

        $response = $this->getJson("/api/v1/restaurants/{$restaurant->id}");

        $response->assertOk()
            ->assertJsonPath('data.name', 'Test Resto')
            ->assertJsonPath('data.price_label', 'RpRp')
            ->assertJsonPath('data.categories.0.name', 'Kafe')
            ->assertJsonPath('data.opening_hours.0.day', 'Senin')
            ->assertJsonPath('data.my_review.rating', 5)
            ->assertJsonPath('data.my_review.comment', 'My awesome review')
            ->assertJsonPath('data.top_reviews.0.comment', 'Other review');
    }

    public function test_restaurant_menus(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $restaurant = Restaurant::factory()->create(['status' => 'active']);

        MenuItem::factory()->create([
            'restaurant_id' => $restaurant->id,
            'group_name' => 'Minuman',
            'name' => 'Es Teh Manis',
            'price' => 5000,
        ]);

        MenuItem::factory()->create([
            'restaurant_id' => $restaurant->id,
            'group_name' => 'Makanan',
            'name' => 'Nasi Goreng',
            'price' => 20000,
        ]);

        $response = $this->getJson("/api/v1/restaurants/{$restaurant->id}/menus");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'group',
                        'items' => [
                            '*' => [
                                'id',
                                'name',
                                'price_label',
                                'description',
                                'photo_url',
                                'is_available',
                            ],
                        ],
                    ],
                ],
            ]);

        $groups = collect($response->json('data'))->pluck('group')->toArray();
        $this->assertContains('Makanan', $groups);
        $this->assertContains('Minuman', $groups);
    }

    public function test_restaurant_reviews_list(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $restaurant = Restaurant::factory()->create(['status' => 'active']);
        Review::factory()->count(3)->create(['restaurant_id' => $restaurant->id]);

        $response = $this->getJson("/api/v1/restaurants/{$restaurant->id}/reviews");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'rating',
                        'comment',
                        'user' => ['id', 'name', 'avatar_url'],
                        'created_label',
                        'is_mine',
                    ],
                ],
                'meta' => ['next_cursor'],
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_upsert_and_delete_review(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $restaurant = Restaurant::factory()->create(['status' => 'active']);

        // Create review
        $res = $this->putJson("/api/v1/restaurants/{$restaurant->id}/review", [
            'rating' => 5,
            'comment' => 'Mantap sekali!',
        ]);

        $res->assertOk()
            ->assertJsonPath('data.rating', 5)
            ->assertJsonPath('data.comment', 'Mantap sekali!')
            ->assertJsonPath('data.is_mine', true);

        $restaurant->refresh();
        $this->assertEquals(1, $restaurant->rating_count);
        $this->assertEquals(5.0, $restaurant->rating_avg);

        // Update review
        $resUpdate = $this->putJson("/api/v1/restaurants/{$restaurant->id}/review", [
            'rating' => 3,
            'comment' => 'Biasa aja ternyata',
        ]);

        $resUpdate->assertOk()
            ->assertJsonPath('data.rating', 3)
            ->assertJsonPath('data.comment', 'Biasa aja ternyata');

        $restaurant->refresh();
        $this->assertEquals(1, $restaurant->rating_count);
        $this->assertEquals(3.0, $restaurant->rating_avg);

        // Delete review
        $resDelete = $this->deleteJson("/api/v1/restaurants/{$restaurant->id}/review");
        $resDelete->assertNoContent();

        $restaurant->refresh();
        $this->assertEquals(0, $restaurant->rating_count);
        $this->assertEquals(0.0, $restaurant->rating_avg);

        // Re-create after soft delete should restore without unique error
        $resRecreate = $this->putJson("/api/v1/restaurants/{$restaurant->id}/review", [
            'rating' => 4,
            'comment' => 'Beli lagi, lumayan enak',
        ]);
        $resRecreate->assertOk();
        $restaurant->refresh();
        $this->assertEquals(1, $restaurant->rating_count);
        $this->assertEquals(4.0, $restaurant->rating_avg);
    }

    public function test_favorites_flow(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $restaurant = Restaurant::factory()->create(['status' => 'active', 'name' => 'Favorit Resto']);

        // Add to favorite
        $this->postJson("/api/v1/restaurants/{$restaurant->id}/favorite")->assertNoContent();

        $this->assertTrue($user->favorites()->where('restaurant_id', $restaurant->id)->exists());

        // Get favorites screen
        $res = $this->getJson('/api/v1/screens/favorites');
        $res->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $restaurant->id)
            ->assertJsonPath('data.0.is_favorited', true);

        // Remove from favorite
        $this->deleteJson("/api/v1/restaurants/{$restaurant->id}/favorite")->assertNoContent();
        $this->assertFalse($user->favorites()->where('restaurant_id', $restaurant->id)->exists());

        $resEmpty = $this->getJson('/api/v1/screens/favorites');
        $resEmpty->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_profile_screen_and_update_profile(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['name' => 'Original Name']);
        Sanctum::actingAs($user);

        $restaurant = Restaurant::factory()->create(['status' => 'active']);
        Review::factory()->create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'rating' => 5,
        ]);

        $res = $this->getJson('/api/v1/screens/profile');
        $res->assertOk()
            ->assertJsonPath('data.user.name', 'Original Name')
            ->assertJsonPath('data.review_count', 1)
            ->assertJsonCount(1, 'data.my_reviews');

        // Update profile
        $file = UploadedFile::fake()->image('avatar.jpg');
        $resUpdate = $this->putJson('/api/v1/me', [
            'name' => 'Updated Name',
            'avatar' => $file,
        ]);

        $resUpdate->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
        $this->assertNotNull($resUpdate->json('data.avatar_url'));

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        Storage::disk('public')->assertExists($user->avatar_path);
    }
}
