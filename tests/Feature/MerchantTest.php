<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\OpeningHour;
use App\Models\Restaurant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MerchantTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_access_merchant_endpoints(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/merchant/screens/dashboard')->assertStatus(403);
        $this->getJson('/api/v1/merchant/store')->assertStatus(403);
    }

    public function test_merchant_dashboard_without_store(): void
    {
        $merchant = User::factory()->create(['role' => 'merchant']);
        Sanctum::actingAs($merchant);

        $response = $this->getJson('/api/v1/merchant/screens/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.store', null);
    }

    public function test_merchant_dashboard_with_store(): void
    {
        $merchant = User::factory()->create(['role' => 'merchant']);
        Sanctum::actingAs($merchant);

        $restaurant = Restaurant::factory()->create([
            'owner_id' => $merchant->id,
            'status' => 'pending',
            'rating_avg' => 4.5,
            'rating_count' => 10,
        ]);

        MenuItem::factory()->count(3)->create(['restaurant_id' => $restaurant->id]);
        Review::factory()->count(2)->create(['restaurant_id' => $restaurant->id]);

        $response = $this->getJson('/api/v1/merchant/screens/dashboard');

        $restaurant->refresh();

        $response->assertOk()
            ->assertJsonPath('data.store.id', $restaurant->id)
            ->assertJsonPath('data.store.status', 'pending')
            ->assertJsonPath('data.store.rating_label', number_format($restaurant->rating_avg, 1))
            ->assertJsonPath('data.store.menu_count', 3)
            ->assertJsonCount(2, 'data.latest_reviews');
    }

    public function test_merchant_create_and_update_store(): void
    {
        Storage::fake('public');

        $merchant = User::factory()->create(['role' => 'merchant']);
        Sanctum::actingAs($merchant);

        $cat1 = Category::factory()->create(['name' => 'Kopi']);
        $cat2 = Category::factory()->create(['name' => 'Snack']);

        $cover = UploadedFile::fake()->image('cover.jpg');

        $payload = [
            'name' => 'Kedai Kopi Saya',
            'description' => 'Tempat santai',
            'address' => 'Jl. Pahlawan No. 10',
            'phone' => '08123456789',
            'lat' => -6.9920,
            'lng' => 110.4210,
            'price_level' => 2,
            'categories' => [$cat1->id, $cat2->id],
            'opening_hours' => [
                [
                    'day_of_week' => 1,
                    'open_time' => '09:00',
                    'close_time' => '22:00',
                    'is_closed' => false,
                ],
                [
                    'day_of_week' => 0,
                    'is_closed' => true,
                ],
            ],
            'cover' => $cover,
        ];

        // Create store
        $res = $this->postJson('/api/v1/merchant/store', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('data.name', 'Kedai Kopi Saya')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonCount(2, 'data.categories')
            ->assertJsonCount(2, 'data.opening_hours');

        $this->assertDatabaseHas('restaurants', [
            'owner_id' => $merchant->id,
            'name' => 'Kedai Kopi Saya',
            'status' => 'pending',
        ]);

        // Attempt duplicate store creation
        $resDuplicate = $this->postJson('/api/v1/merchant/store', $payload);
        $resDuplicate->assertStatus(422)
            ->assertJsonPath('code', 'store_already_exists');

        // Get store
        $resGet = $this->getJson('/api/v1/merchant/store');
        $resGet->assertOk()
            ->assertJsonPath('data.name', 'Kedai Kopi Saya');

        // Update store
        $resUpdate = $this->putJson('/api/v1/merchant/store', [
            'name' => 'Kedai Kopi & Teh',
            'price_level' => 3,
        ]);

        $resUpdate->assertOk()
            ->assertJsonPath('data.name', 'Kedai Kopi & Teh')
            ->assertJsonPath('data.price_level', 3);
    }

    public function test_merchant_menu_crud(): void
    {
        Storage::fake('public');

        $merchant = User::factory()->create(['role' => 'merchant']);
        Sanctum::actingAs($merchant);

        $restaurant = Restaurant::factory()->create(['owner_id' => $merchant->id]);

        $photo = UploadedFile::fake()->image('menu.jpg');

        // Create menu
        $resCreate = $this->postJson('/api/v1/merchant/menus', [
            'group_name' => 'Minuman',
            'name' => 'Es Kopi Aren',
            'description' => 'Kopi gula aren murni',
            'price' => 18000,
            'is_available' => true,
            'photo' => $photo,
        ]);

        $resCreate->assertStatus(201)
            ->assertJsonPath('data.name', 'Es Kopi Aren')
            ->assertJsonPath('data.price_label', 'Rp 18.000')
            ->assertJsonPath('data.is_available', true);

        $menuId = $resCreate->json('data.id');

        // List menus
        $resList = $this->getJson('/api/v1/merchant/menus');
        $resList->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $menuId);

        // Update menu (toggle available)
        $resUpdate = $this->putJson("/api/v1/merchant/menus/{$menuId}", [
            'is_available' => false,
            'price' => 20000,
        ]);

        $resUpdate->assertOk()
            ->assertJsonPath('data.is_available', false)
            ->assertJsonPath('data.price_label', 'Rp 20.000');

        // Delete menu
        $resDelete = $this->deleteJson("/api/v1/merchant/menus/{$menuId}");
        $resDelete->assertNoContent();

        $this->assertSoftDeleted('menu_items', ['id' => $menuId]);
    }

    public function test_merchant_reviews_list(): void
    {
        $merchant = User::factory()->create(['role' => 'merchant']);
        Sanctum::actingAs($merchant);

        $restaurant = Restaurant::factory()->create(['owner_id' => $merchant->id]);
        Review::factory()->count(4)->create(['restaurant_id' => $restaurant->id]);

        $response = $this->getJson('/api/v1/merchant/reviews');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'rating', 'comment', 'user', 'created_label'],
                ],
                'meta' => ['next_cursor'],
            ])
            ->assertJsonCount(4, 'data');
    }
}
