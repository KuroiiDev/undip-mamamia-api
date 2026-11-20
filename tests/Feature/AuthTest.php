<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_successfully(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Budi Baru',
            'email' => 'budibaru@mamamia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'role', 'status', 'avatar_url'],
                ],
            ])
            ->assertJson([
                'data' => [
                    'user' => [
                        'name' => 'Budi Baru',
                        'email' => 'budibaru@mamamia.test',
                        'role' => 'user',
                        'status' => 'active',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'budibaru@mamamia.test',
            'role' => 'user',
        ]);
    }

    public function test_merchant_can_register_successfully(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Pedagang Baru',
            'email' => 'pedagang@mamamia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'merchant',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'user' => [
                        'role' => 'merchant',
                    ],
                ],
            ]);
    }

    public function test_admin_cannot_register_via_api(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Admin Baru',
            'email' => 'adminbaru@mamamia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 'validation_failed',
            ]);
    }

    public function test_register_validation_errors(): void
    {
        $response = $this->postJson('/api/v1/register', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'code',
                'errors' => ['name', 'email', 'password', 'role'],
            ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@mamamia.test',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'testuser@mamamia.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'role', 'status'],
                ],
            ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'testuser@mamamia.test',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'testuser@mamamia.test',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'code' => 'unauthenticated',
            ]);
    }

    public function test_banned_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'banned@mamamia.test',
            'password' => Hash::make('password123'),
            'status' => 'banned',
            'banned_reason' => 'Pelanggaran spam.',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'banned@mamamia.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'code' => 'account_banned',
                'reason' => 'Pelanggaran spam.',
            ]);
    }

    public function test_authenticated_user_can_get_me(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => 'user',
                    'status' => 'active',
                ],
            ]);
    }

    public function test_banned_user_request_is_rejected_by_middleware(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'banned',
            'banned_reason' => 'Akun diblokir.',
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/me');

        $response->assertStatus(403)
            ->assertJson([
                'code' => 'account_banned',
                'reason' => 'Akun diblokir.',
            ]);

        // Token should be revoked
        $this->assertDatabaseEmpty('personal_access_tokens');
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/logout');

        $response->assertStatus(204);

        $this->assertDatabaseEmpty('personal_access_tokens');
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401)
            ->assertJson([
                'code' => 'unauthenticated',
            ]);
    }
}
