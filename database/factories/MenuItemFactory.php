<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'group_name' => fake()->randomElement(['Makanan', 'Minuman', 'Camilan']),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(10000, 50000),
            'photo_path' => null,
            'is_available' => true,
        ];
    }
}
