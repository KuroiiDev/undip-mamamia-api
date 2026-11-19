<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_api_ping_returns_a_successful_response(): void
    {
        $response = $this->getJson('/api/v1/ping');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'pong' => true,
                ],
            ]);
    }
}
