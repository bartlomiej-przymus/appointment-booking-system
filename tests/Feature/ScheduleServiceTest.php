<?php

use App\Enums\AppointmentStatus;
use App\Enums\DayType;
use App\Enums\ScheduleType;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Day;
use App\Models\Schedule;
use App\Models\Slot;
use App\Models\User;
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

it('returns available dates and time slots for current month when schedule is set to daily', function () {
    $testDate = Carbon::parse('1st November 2024');
    Carbon::setTestNow($testDate);

    /**
     * Current date is set for November 2024
     *
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

it('returns available dates and time slots for current month when schedule is set to weekly', function () {
    $testDate = Carbon::parse('1st November 2024');
    Carbon::setTestNow($testDate);

    /**
     * Current date is set for 1st of November 2024
     *
     * Four Availability Time Slots are created
     * Two for weekdays and 2 for Sundays
     * Type weekly Schedule is created and set to be
     * active.
     * I create three days for which schedule should
     * repeat weekly Monday and Friday and Sunday.
     *
     * System has date padding hard-coded
     * to allow new appointments to be booked
     * at lest 2 days in advance giving admin
     * time to prepare.
     *
     * Resulting number of days by doing manual
     * count starting on 1st November 2024 is 11.
     *
     * First active day should be 4th as system
     * adds 2 days of buffer ahead. (we don't see
     * 1st of November which is Friday or
     * 3rd which is Sunday)
     *
     * Result should only contain 4 Mondays and 4 Fridays
     */
    $availabilityWeekday = Availability::factory()->create();

    $slotsWeekday = Slot::factory()->createMany(
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

    $availabilityWeekday->slots()->attach($slotsWeekday);

    $availabilitySunday = Availability::factory()->create();

    $slotsSunday = Slot::factory()->createMany([
        [
            'start_time' => '13:00',
            'end_time' => '13:45',
        ],
        [
            'start_time' => '16:00',
            'end_time' => '16:45',
        ],
    ]);

    $availabilitySunday->slots()->attach($slotsSunday);

    $schedule = Schedule::factory()
        ->active()
        ->create([
            'type' => ScheduleType::Weekly->value,
            'excluded_days' => [],
        ]);

    Day::factory()
        ->for($availabilityWeekday)
        ->count(2)
        ->sequence(
            ['type' => DayType::Monday->value],
            ['type' => DayType::Friday->value],
        )->create();

    Day::factory()
        ->for($availabilitySunday)
        ->create([
            'type' => DayType::Sunday->value,
        ]);

    $days = Day::all();

    $schedule->days()->attach($days);

    $availableDatesForMonth = (new ScheduleService)
        ->getAvailableDatesForMonth(now());

    $availableSundays = $availableDatesForMonth->filter(
        function ($value, $key) {
            return in_array($key, ['2024-11-10', '2024-11-17', '2024-11-24']);
        });

    $availableWeekdays = $availableDatesForMonth->diffKeys($availableSundays);

    $sundayAvailabilitySlots = $availabilitySunday->slots
        ->pluck('start_time')
        ->map(fn ($time) => $time->format('H:i'))
        ->toArray();

    $weekdayAvailabilitySlots = $availabilityWeekday->slots
        ->pluck('start_time')
        ->map(fn ($time) => $time->format('H:i'))
        ->toArray();

    expect($availableDatesForMonth->isEmpty())
        ->toBeFalse()
        ->and($availableDatesForMonth)
        ->toBeCollection()
        ->toHaveCount(11)
        ->and($availableSundays)
        ->toHaveCount(3)
        ->each->toMatchArray($sundayAvailabilitySlots)
        ->and($availableWeekdays)
        ->each->toMatchArray($weekdayAvailabilitySlots)
        ->and($availableDatesForMonth->keys()->first())
        ->toBe('2024-11-04');
});

it('returns available dates and time slots for current month when schedule is set to custom', function () {
    $testDate = Carbon::parse('1st November 2024');
    Carbon::setTestNow($testDate);

    /**
     * Current date is set for 1st of November 2024
     *
     *
     * Four Availability Time Slots are created
     * They are assigned to 4 predefined dates in a month
     *
     * Type custom Schedule is created and has
     * active_from and active_to dates set to be
     * active.
     *
     * I create four days for which schedule should not
     * repeat: 2024-11-02, 2024-11-17, 2024-11-22, 2024-11-30
     *
     * In case of custom schedule date padding should not apply.
     *
     * Result should only contain 4 Days as specified.
     */
    Availability::factory()
        ->hasAttached(Slot::factory()
            ->createMany([
                [
                    'start_time' => '09:00',
                    'end_time' => '09:45',
                ],
                [
                    'start_time' => '10:00',
                    'end_time' => '10:45',
                ],
            ])
        )->create();

    Availability::factory()
        ->hasAttached(Slot::factory()
            ->createMany([
                [
                    'start_time' => '11:00',
                    'end_time' => '11:45',
                ],
                [
                    'start_time' => '12:00',
                    'end_time' => '12:45',
                ],
            ])
        )->create();

    Availability::factory()
        ->hasAttached(Slot::factory()
            ->createMany([
                [
                    'start_time' => '13:00',
                    'end_time' => '13:45',
                ],
                [
                    'start_time' => '14:00',
                    'end_time' => '14:45',
                ],
            ])
        )->create();

    Availability::factory()
        ->hasAttached(Slot::factory()
            ->createMany([
                [
                    'start_time' => '15:00',
                    'end_time' => '15:45',
                ],
                [
                    'start_time' => '16:00',
                    'end_time' => '16:45',
                ],
            ])
        )->create();

    $schedule = Schedule::factory()
        ->create([
            'active_from' => now()->startOfMonth(),
            'active_to' => now()->endOfMonth(),
            'type' => ScheduleType::Custom->value,
            'excluded_days' => [],
        ]);

    Day::factory()
        ->count(4)
        ->sequence(
            ['date' => '2024-11-02'],
            ['date' => '2024-11-17'],
            ['date' => '2024-11-22'],
            ['date' => '2024-11-30'],
        )->create();

    $availabilities = Availability::all();
    $days = Day::all();

    $days->each(function ($day, $index) use ($availabilities) {
        $day->availability()->associate($availabilities[$index])->save();
    });

    $schedule->days()->attach($days);

    $availableDatesForMonth = (new ScheduleService)
        ->getAvailableDatesForMonth(now());

    expect($availableDatesForMonth->isEmpty())
        ->toBeFalse()
        ->and($availableDatesForMonth)
        ->toBeCollection()
        ->toHaveCount(4)
        ->each->toHaveCount(2)
        ->and($availableDatesForMonth->toArray()['2024-11-02'])
        ->toBe(['09:00', '10:00'])
        ->and($availableDatesForMonth->toArray()['2024-11-17'])
        ->toBe(['11:00', '12:00'])
        ->and($availableDatesForMonth->toArray()['2024-11-22'])
        ->toBe(['13:00', '14:00'])
        ->and($availableDatesForMonth->toArray()['2024-11-30'])
        ->toBe(['15:00', '16:00'])
        ->and($availableDatesForMonth->keys()->toArray())
        ->toBe(Day::pluck('date')
            ->map(fn ($date) => $date->toDateString())
            ->toArray()
        );
});

it('can book an appointment', function () {
    $user = User::factory()->create();
    $testDate = Carbon::parse('1st November 2024');

    Carbon::setTestNow($testDate);

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

    $schedule = Schedule::factory()
        ->active()
        ->for($availability)
        ->create([
            'type' => ScheduleType::Daily->value,
            'excluded_days' => [],
        ]);

    $appointment = (new ScheduleService)
        ->bookAppointment(
            date: '2024-11-04',
            timeSlot: '11:00',
            user: $user,
            schedule: $schedule
        );

    expect($appointment)
        ->toBeInstanceOf(Appointment::class)
        ->and($appointment->date->toDateString())
        ->toBe('2024-11-04')
        ->and($appointment->time_slot->format('H:i'))
        ->toBe('11:00')
        ->and($appointment->user->name)
        ->toBe($user->name)
        ->and($appointment->schedule->name)
        ->toBe($schedule->name)
        ->and($appointment->status)
        ->toBe(AppointmentStatus::Pending);
});

it('will throw exception when booking appointment that is booked and pending', function () {
    $user = User::factory()->create();
    $testDate = Carbon::parse('1st November 2024');

    Carbon::setTestNow($testDate);

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

    $schedule = Schedule::factory()
        ->active()
        ->for($availability)
        ->for($user)
        ->create([
            'type' => ScheduleType::Daily->value,
            'excluded_days' => [],
        ]);

    (new ScheduleService)->bookAppointment(
        date: '2024-11-04',
        timeSlot: '11:00',
        user: $user,
        schedule: $schedule
    );

    (new ScheduleService)->bookAppointment(
        date: '2024-11-04',
        timeSlot: '11:00',
        user: $user,
        schedule: $schedule
    );
})->throws(Exception::class, 'Appointment slot is no longer available');

it('will re-book appointment with correct details in place of cancelled one', function () {
    $user = User::factory()->create();
    $testDate = Carbon::parse('1st November 2024');

    Carbon::setTestNow($testDate);

    $availability = Availability::factory()->create([
        'appointment_duration' => 45,
    ]);

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

    $schedule = Schedule::factory()
        ->active()
        ->for($availability)
        ->for($user)
        ->create([
            'type' => ScheduleType::Daily->value,
            'excluded_days' => [],
        ]);

    (new Appointment)->create([
        'user_id' => $user->getKey(),
        'schedule_id' => $schedule->getKey(),
        'date' => '2024-11-04',
        'time_slot' => '11:00',
        'status' => AppointmentStatus::Cancelled,
        'duration' => $availability->appointment_duration,
    ]);

    expect(Appointment::count())
        ->toBe(1)
        ->and(Appointment::first()->status)
        ->toBe(AppointmentStatus::Cancelled)
        ->and(Appointment::first()->user->getKey())
        ->toBe($user->getKey());

    $newUser = User::factory()->create();

    (new ScheduleService)->bookAppointment(
        date: '2024-11-04',
        timeSlot: '11:00',
        user: $newUser,
        schedule: $schedule
    );

    expect(Appointment::count())
        ->toBe(1)
        ->and(Appointment::first()->status)
        ->toBe(AppointmentStatus::Pending)
        ->and(Appointment::first()->user->getKey())
        ->toBe($newUser->getKey());
});
