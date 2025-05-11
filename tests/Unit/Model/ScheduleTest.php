<?php

namespace Tests\Unit\Model;

use App\Models\Schedule;

it('can check if current date is within schedule active date range', function () {
    $activeSchedule = Schedule::factory()->make([
        'active_from' => now()->subDay(),
        'active_to' => now()->addDay(),
    ]);

    $inactiveSchedule = Schedule::factory()->make([
        'active_from' => now()->subWeek(),
        'active_to' => now()->subDay(),
    ]);

    expect($activeSchedule->isWithinActivePeriod())->toBeTrue()
        ->and($inactiveSchedule->isWithinActivePeriod())->toBeFalse();
});

it('can check if schedules set to active exist', function () {
    $activeSchedule = Schedule::factory()->active()->create();
    $newSchedule = Schedule::factory()->create([
        'active' => false,
    ]);

    expect($newSchedule->activeScheduleExists())->toBeTrue();

    $activeSchedule->update(['active' => false]);

    expect($newSchedule->activeScheduleExists())->toBeFalse();
});

it('can check if it has valid date range', function () {
    $schedule = Schedule::factory()
        ->create([
            'active_from' => null,
            'active_to' => null,
        ]);

    expect($schedule->hasValidDateRange())->toBeFalse();

    $schedule->update([
        'active_from' => now()->subDay(),
        'active_to' => now()->addDay(),
    ]);

    expect($schedule->hasValidDateRange())->toBeTrue();
});

it('can check if schedule is active', function () {
    $schedule = Schedule::factory()->active()->create([
        'active_from' => null,
        'active_to' => null,
    ]);
    expect($schedule->isActive())->toBeTrue();

    $schedule->update(['active' => false]);

    expect($schedule->isActive())->toBeFalse();
});

it('can show schedule as inactive if there is other set to active', function () {
    $activeSchedule = Schedule::factory()
        ->active()
        ->create([
            'active_from' => null,
            'active_to' => null,
        ]);

    $schedule = Schedule::factory()->create([
        'active' => false,
        'active_from' => now()->subWeek(),
        'active_to' => now()->addWeek(),
    ]);

    expect($schedule->isActive())->toBeFalse();

    $activeSchedule->update(['active' => false]);

    expect($schedule->isActive())->toBeTrue();
});
