<?php

use App\Models\Schedule;
use App\Services\ScheduleService;

it('can retrieve schedule set to active', function () {
    Schedule::factory()->count(5)->create([
        'active' => false,
        'active_from' => null,
        'active_to' => null,
    ]);

    $activeSchedule = Schedule::factory()->active()->create();

    $retrievedSchedule = (new ScheduleService)->getActiveSchedule();

    expect($retrievedSchedule)
        ->toBeInstanceOf(Schedule::class)
        ->and($retrievedSchedule->active)
        ->toBeTrue()
        ->and($retrievedSchedule->name)
        ->toBe($activeSchedule->name);
});

it('can retrieve schedule that is active based on current date range', function () {
    $activeSchedule = Schedule::factory()->create([
        'active' => false,
        'active_from' => now()->subWeek(),
        'active_to' => now()->addWeek(),
    ]);
    Schedule::factory()->count(5)->create([
        'active' => false,
        'active_from' => null,
        'active_to' => null,
    ]);

    $retrievedSchedule = (new ScheduleService)->getActiveSchedule();

    expect($retrievedSchedule)
        ->toBeInstanceOf(Schedule::class)
        ->and($retrievedSchedule->active)
        ->toBeFalse()
        ->and($retrievedSchedule->name)
        ->toBe($activeSchedule->name);
});

it('can retrieve schedule that is set to active when another schedule has valid date range set', function () {
    Schedule::factory()->create([
        'name' => 'Active Date Range Schedule',
        'active' => false,
        'active_from' => now()->subWeek(),
        'active_to' => now()->addWeek(),
    ]);

    $activeSchedule = Schedule::factory()->active()->create();

    $retrievedSchedule = (new ScheduleService)->getActiveSchedule();

    expect($retrievedSchedule)
        ->toBeInstanceOf(Schedule::class)
        ->and($retrievedSchedule->active)
        ->toBeTrue()
        ->and($retrievedSchedule->name)
        ->toBe($activeSchedule->name);
});

it('returns null when no active date range schedule is currently set', function () {
    $scheduleService = new ScheduleService;

    Schedule::factory()->create([
        'active' => false,
        'active_from' => now()->subMonth(),
        'active_to' => now()->subWeek(),
    ]);

    Schedule::factory()->create([
        'active' => false,
        'active_from' => now()->addMonth(),
        'active_to' => now()->addWeek(),
    ]);

    expect($scheduleService->getActiveSchedule())->toBeNull();
});
