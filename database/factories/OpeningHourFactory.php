<?php

namespace Database\Factories;

use App\Models\OpeningHour;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OpeningHourFactory extends Factory
{
    protected $model = OpeningHour::class;

    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'day_of_week' => 1,
            'open_time' => '08:00:00',
            'close_time' => '21:00:00',
            'is_closed' => false,
        ];
    }
}
