<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $merchant;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->merchant = User::factory()->create([
            'role' => 'merchant',
            'status' => 'active',
        ]);

        $this->regularUser = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->getJson('/api/v1/admin/screens/dashboard');
        $response->assertStatus(403);

        Sanctum::actingAs($this->merchant);

        $response = $this->getJson('/api/v1/admin/screens/dashboard');
        $response->assertStatus(403);
    }

    public function test_admin_dashboard_metrics(): void
    {
        Sanctum::actingAs($this->admin);

        Restaurant::factory()->create([
            'owner_id' => $this->merchant->id,
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/v1/admin/screens/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'store_count',
                    'user_count',
                    'pending_count',
                    'open_report_count',
                    'pending_stores' => [
                        '*' => [
                            'id',
                            'name',
                            'owner_name',
                            'address',
                            'created_at',
                            'cover_url',
                        ],
                    ],
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals(1, $data['store_count']);
        $this->assertEquals(1, $data['pending_count']);
        $this->assertCount(1, $data['pending_stores']);
    }

    public function test_admin_can_list_and_approve_stores(): void
    {
        Sanctum::actingAs($this->admin);

        $store = Restaurant::factory()->create([
            'owner_id' => $this->merchant->id,
            'status' => 'pending',
        ]);

        // List stores
        $listRes = $this->getJson('/api/v1/admin/stores?status=pending');
        $listRes->assertOk()
            ->assertJsonCount(1, 'data');

        // Approve store
        $approveRes = $this->postJson("/api/v1/admin/stores/{$store->id}/approve");
        $approveRes->assertOk();

        $this->assertDatabaseHas('restaurants', [
            'id' => $store->id,
            'status' => 'active',
            'status_reason' => null,
        ]);
    }

    public function test_admin_can_ban_and_delete_store_with_reason(): void
    {
        Sanctum::actingAs($this->admin);

        $store = Restaurant::factory()->create([
            'owner_id' => $this->merchant->id,
            'status' => 'active',
        ]);

        // Ban without reason fails 422
        $failBan = $this->postJson("/api/v1/admin/stores/{$store->id}/ban", []);
        $failBan->assertStatus(422);

        // Ban with reason
        $banRes = $this->postJson("/api/v1/admin/stores/{$store->id}/ban", [
            'reason' => 'Melanggar ketentuan kebersihan dan ketertiban.',
        ]);
        $banRes->assertOk();

        $this->assertDatabaseHas('restaurants', [
            'id' => $store->id,
            'status' => 'banned',
            'status_reason' => 'Melanggar ketentuan kebersihan dan ketertiban.',
        ]);

        // Delete without reason fails 422
        $failDelete = $this->deleteJson("/api/v1/admin/stores/{$store->id}", []);
        $failDelete->assertStatus(422);

        // Delete with reason soft deletes
        $deleteRes = $this->deleteJson("/api/v1/admin/stores/{$store->id}", [
            'reason' => 'Permintaan penutupan permanen.',
        ]);
        $deleteRes->assertOk();

        $this->assertSoftDeleted('restaurants', [
            'id' => $store->id,
        ]);
    }

    public function test_admin_can_list_ban_and_unban_users(): void
    {
        Sanctum::actingAs($this->admin);

        // List users
        $listRes = $this->getJson('/api/v1/admin/users?role=user');
        $listRes->assertOk()
            ->assertJsonCount(1, 'data');

        // Create token for target user to verify revocation
        $targetUser = $this->regularUser;
        $targetUser->createToken('test-token');
        $this->assertCount(1, $targetUser->tokens);

        // Ban without reason fails 422
        $failBan = $this->postJson("/api/v1/admin/users/{$targetUser->id}/ban", []);
        $failBan->assertStatus(422);

        // Ban with reason
        $banRes = $this->postJson("/api/v1/admin/users/{$targetUser->id}/ban", [
            'reason' => 'Spam ulasan kasar berulang kali.',
        ]);
        $banRes->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'status' => 'banned',
            'banned_reason' => 'Spam ulasan kasar berulang kali.',
        ]);

        // Tokens revoked
        $this->assertCount(0, $targetUser->fresh()->tokens);

        // Unban
        $unbanRes = $this->postJson("/api/v1/admin/users/{$targetUser->id}/unban");
        $unbanRes->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'status' => 'active',
            'banned_reason' => null,
        ]);
    }
}
