<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AvailabilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word,
            'start_time' => fake()->time('H:i'),
            'end_time' => fake()->time('H:i'),
            'appointment_duration' => fake()->time('i'),
            'break' => fake()->time('i'),
        ];
    }
}
