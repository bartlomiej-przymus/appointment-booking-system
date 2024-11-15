<?php

namespace Database\Factories;

use App\Enums\DayType;
use App\Enums\ScheduleType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ScheduleFactory extends Factory
{
    public function definition(): array
    {
        $activeFrom = fake()->dateTimeBetween('-1 week', '-1 day');

        return [
            'name' => Str::ucfirst(fake()->word()).' Schedule',
            'type' => fake()->randomElement(ScheduleType::values()),
            'excluded_days' => fake()->randomElements(DayType::values(), 2),
            'active' => fake()->boolean(),
            'active_from' => $activeFrom,
            'active_to' => fake()->dateTimeBetween($activeFrom, '+1 month'),
        ];
    }

    public function active(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
            'active_from' => null,
            'active_to' => null,
        ]);
    }
}
