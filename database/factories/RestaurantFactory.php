<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RestaurantFactory extends Factory
{
    protected $model = Restaurant::class;

    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->create(['role' => 'merchant'])->id,
            'name' => fake()->company(),
            'description' => fake()->paragraph(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'lat' => -6.99 + (fake()->randomNumber(4) / 100000),
            'lng' => 110.42 + (fake()->randomNumber(4) / 100000),
            'price_level' => fake()->numberBetween(1, 3),
            'status' => 'active',
            'status_reason' => null,
            'cover_path' => null,
            'rating_avg' => 0.0,
            'rating_count' => 0,
            'timezone' => 'Asia/Jakarta',
        ];
    }
}
