<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\ScheduleType;
use App\Models\Appointment;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ScheduleService
{
    public function getActiveSchedule(): ?Schedule
    {
        return Schedule::with(
            'user',
            'availability.slots',
            'days.availability.slots'
        )->where('active', true)
            ->first()
            ?? Schedule::with(
                'user',
                'availability.slots',
                'days.availability.slots'
            )->where('active_from', '<=', now())
                ->where('active_to', '>=', now())
                ->first();
    }

    public function getAvailableDatesForMonth(Carbon $date): Collection
    {
        $activeSchedule = $this->getActiveSchedule();

        if ($activeSchedule === null) {
            return collect();
        }

        return match ($activeSchedule->type) {
            ScheduleType::Daily => $this->getAvailableTimeslotsForDailySchedule($date, $activeSchedule),
            ScheduleType::Weekly => $this->getAvailableTimeslotsForWeeklySchedule($date, $activeSchedule),
            ScheduleType::Custom => $this->getAvailableTimeSlotsForCustomSchedule($date, $activeSchedule),
            default => collect()
        };
    }

    protected function getAvailableTimeslotsForDailySchedule(
        Carbon $date,
        Schedule $activeSchedule
    ): Collection {
        $bookedAppointments = $this->getBookedAppointmentsForMonth($date);
        $dateRange = $this->calculateDateRange($date);
        $availableSlots = $this->formatAvailableSlots(
            $activeSchedule->availability->slots
        );
        $excludedDays = collect($activeSchedule->excluded_days);

        return $this->processDateRange(
            $dateRange->get('start'),
            $dateRange->get('end'),
            function (Carbon $currentDate) use (
                $excludedDays,
                $availableSlots,
                $bookedAppointments
            ) {
                $dayOfWeek = Str::lower(($currentDate->format('l')));

                if ($excludedDays->contains($dayOfWeek)) {
                    return null;
                }

                return $this->getAvailableSlotsForDay(
                    $currentDate,
                    $availableSlots,
                    $bookedAppointments
                );
            }
        );
    }

    protected function getAvailableTimeslotsForWeeklySchedule(
        Carbon $date,
        Schedule $activeSchedule
    ): Collection {
        $bookedAppointments = $this->getBookedAppointmentsForMonth($date);
        $dateRange = $this->calculateDateRange($date);

        $scheduleDays = $activeSchedule
            ->days
            ->keyBy(
                fn ($day) => $day->type->value
            );

        return $this->processDateRange(
            $dateRange->get('start'),
            $dateRange->get('end'),
            function (Carbon $currentDate) use (
                $scheduleDays,
                $bookedAppointments
            ) {
                $dayOfWeek = Str::lower(
                    $currentDate->format('l')
                );

                if (! $scheduleDays->has($dayOfWeek)) {
                    return null;
                }

                $scheduleDay = $scheduleDays
                    ->get($dayOfWeek);

                return $this->getAvailableSlotsForDay(
                    $currentDate,
                    $this->formatAvailableSlots(
                        $scheduleDay->availability->slots
                    ),
                    $bookedAppointments
                );
            }
        );
    }

    protected function getAvailableTimeSlotsForCustomSchedule(
        Carbon $date,
        Schedule $activeSchedule
    ): Collection {
        $bookedAppointments = $this->getBookedAppointmentsForMonth($date);
        $dateRange = $this->calculateDateRange($date, false);

        $startDate = max(
            $dateRange->get('start'),
            $activeSchedule->active_from
        );

        $endDate = is_null($activeSchedule->active_to)
            ? $dateRange->get('end')
            : min($dateRange->get('end'), $activeSchedule->active_to);

        if ($startDate > $endDate) {
            return collect();
        }

        return $activeSchedule->days
            ->whereBetween('date', [$startDate, $endDate])
            ->mapWithKeys(function ($scheduleDay) use ($bookedAppointments) {
                $dateString = $scheduleDay->date->format('Y-m-d');
                $slots = $this->getAvailableSlotsForDay(
                    $scheduleDay->date,
                    $this->formatAvailableSlots(
                        $scheduleDay->availability->slots
                    ),
                    $bookedAppointments
                );

                return $slots ? [$dateString => $slots] : [];
            })
            ->filter();
    }

    protected function getBookedAppointmentsForMonth(Carbon $date): Collection
    {
        $dateRange = $this->calculateDateRange($date, false);

        return Appointment::query()
            ->whereBetween(
                'date',
                [$dateRange->values()]
            )
            ->whereIn('status', [
                AppointmentStatus::Pending->value,
                AppointmentStatus::Confirmed->value,
                AppointmentStatus::Rescheduled->value,
            ])
            ->orderBy('date')
            ->orderBy('time_slot')
            ->get()
            ->groupBy(function ($appointment) {
                return Carbon::parse($appointment->date)->format('Y-m-d');
            })
            ->map(function (Collection $appointments) {
                return $appointments
                    ->pluck('time_slot')
                    ->map(
                        fn ($time) => Carbon::parse($time)
                            ->format('H:i')
                    );
            });
    }

    protected function formatAvailableSlots(Collection $slots): Collection
    {
        return $slots
            ->pluck('start_time')
            ->map(
                fn (Carbon $time) => $time
                    ->format('H:i')
            );
    }

    protected function getAvailableSlotsForDay(
        Carbon $date,
        Collection $availableSlots,
        Collection $bookedAppointments
    ): ?Collection {
        $dateString = $date
            ->format('Y-m-d');
        $bookedSlotsForDay = $bookedAppointments
            ->get($dateString, collect());
        $availableSlotsForDay = $availableSlots->reject(
            fn ($slot) => $bookedSlotsForDay->contains($slot)
        );

        return $availableSlotsForDay->isEmpty()
            ? null
            : $availableSlotsForDay;
    }

    protected function calculateDateRange(Carbon $date, bool $addDays = true): Collection
    {
        /**
         * We are adding 3 days to the date from which
         * available slots are bookable to give some
         * needed preparation time
         */
        $startDate = $addDays
            ? Carbon::today()
                ->startOfDay()
                ->addDays(3)
            : Carbon::today()
                ->startOfDay();

        $endDate = $date->copy()->endOfMonth();

        if ($date->startOfMonth()->gt($startDate)) {
            $startDate = $date->copy()->startOfMonth();
        }

        return collect([
            'start' => $startDate,
            'end' => $endDate,
        ]);
    }

    protected function processDateRange(
        Carbon $startDate,
        Carbon $endDate,
        callable $processor
    ): Collection {
        return collect()
            ->range(
                from: 0,
                to: $endDate
                    ->startOfDay()
                    ->diffInDays(
                        $startDate->startOfDay(),
                        true
                    )
            )->mapWithKeys(
                function ($dayOffset) use (
                    $startDate,
                    $processor
                ) {
                    $currentDate = $startDate
                        ->copy()
                        ->addDays($dayOffset);
                    $dateString = $currentDate
                        ->format('Y-m-d');
                    $slots = $processor($currentDate);

                    return $slots ? [$dateString => $slots] : [];
                })
            ->filter();
    }

    /**
     * @throws Throwable
     */
    public function bookAppointment(
        string $date,
        string $timeSlot,
        User $user,
        Schedule $schedule
    ): Appointment {
        return DB::transaction(function () use (
            $date,
            $timeSlot,
            $user,
            $schedule
        ) {
            $isAvailable = (new Appointment)->isAvailable($date, $timeSlot, $schedule->getKey());

            if (! $isAvailable) {
                throw new Exception('Appointment slot is no longer available');
            }

            $duration = $this->getAppointmentDuration($schedule, $date);

            return Appointment::updateOrCreate([
                'date' => $date,
                'time_slot' => $timeSlot,
                'schedule_id' => $schedule->getKey(),
            ], [
                'user_id' => $user->getKey(),
                'schedule_id' => $schedule->getKey(),
                'date' => $date,
                'time_slot' => $timeSlot,
                'status' => AppointmentStatus::Pending->value,
                'duration' => $duration,
            ]);
        });
    }

    public function getAppointmentDuration(
        Schedule $schedule,
        string $selectedDate,
    ): int {
        if ($schedule->type->is(ScheduleType::Daily)) {
            return $schedule->availability->appointment_duration;
        }

        $dayOfWeek = Str::lower(now()->parse($selectedDate)->format('l'));

        $day = $schedule->days()
            ->where('type', $dayOfWeek)
            ->first();

        return $day ? $day->availability->appointment_duration : 0;
    }
}
