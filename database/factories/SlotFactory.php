<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SlotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'start_time' => fake()->time('H:i'),
            'end_time' => fake()->time('H:i'),
        ];
    }
}
