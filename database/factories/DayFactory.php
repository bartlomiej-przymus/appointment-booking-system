<?php

namespace Database\Factories;

use App\Enums\DayType;
use Illuminate\Database\Eloquent\Factories\Factory;

class DayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date' => fake()->date(),
            'type' => fake()->randomElement(DayType::values()),
        ];
    }
}
