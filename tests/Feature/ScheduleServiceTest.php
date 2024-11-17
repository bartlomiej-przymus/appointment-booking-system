<?php

use App\Enums\ScheduleType;
use App\Models\Availability;
use App\Models\Schedule;
use App\Models\Slot;
use App\Services\ScheduleService;
use Carbon\Carbon;

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

    expect((new ScheduleService)
        ->getActiveSchedule())
        ->toBeNull();
});

it('returns available dates and time slots for current month when schedule daily', function () {
    $testDate = Carbon::parse('1st November 2024');
    Carbon::setTestNow($testDate);

    /**
     * Current date is set for November 2024
     * (time of writing this test)
     * Two Availability Time Slots are created
     * Type daily Schedule is created set to be
     * active.
     *
     * System has date padding hard-coded
     * to allow new appointments to be booked
     * at lest 2 days in advance giving admin
     * time to prepare.
     *
     * Resulting number of days by doing manual
     * count starting on 1st November 2024 is 27.
     *
     * First active day should be 4th as system
     * adds 2 days of buffer ahead.
     */
    $availability = Availability::factory()->create();

    $slots = Slot::factory()->createMany(
        [
            [
                'start_time' => '11:00',
                'end_time' => '11:45',
            ],
            [
                'start_time' => '12:00',
                'end_time' => '12:45',
            ],
        ]);

    $availability->slots()->attach($slots);

    Schedule::factory()
        ->active()
        ->for($availability)
        ->create([
            'type' => ScheduleType::Daily->value,
            'excluded_days' => [],
        ]);

    $availableDatesForMonth = (new ScheduleService)
        ->getAvailableDatesForMonth(now());

    expect($availableDatesForMonth->isEmpty())
        ->toBeFalse()
        ->and($availableDatesForMonth)
        ->toBeCollection()
        ->toHaveCount(27)
        ->each->toContain('11:00', '12:00')
        ->and($availableDatesForMonth->keys()->first())
        ->toBe('2024-11-04');
});
