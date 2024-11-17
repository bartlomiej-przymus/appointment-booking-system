<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\ScheduleType;
use App\Models\Appointment;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ScheduleService
{
    private const CACHE_PREFIX = 'schedule_service_';

    private const CACHE_TTL = 3600;

    public function getActiveSchedule(): ?Schedule
    {
        return Cache::remember(
            self::CACHE_PREFIX.'active_schedule',
            self::CACHE_TTL,
            function () {
                return Schedule::with(
                    'availability.slots',
                    'days.availability.slots'
                )->where('active', true)
                    ->first()
                    ?? Schedule::with(
                        'availability.slots',
                        'days.availability.slots'
                    )->where('active_from', '<=', now())
                        ->where('active_to', '>=', now())
                        ->first();
            });
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
        $dateRange = $this->calculateDateRange($date);

        $startDate = max(
            $dateRange->get('start'),
            $activeSchedule->active_from
        );
        $endDate = min(
            $dateRange->get('end'),
            $activeSchedule->active_to
        );

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
        $dateRange = $this->calculateDateRange($date);
        $cacheKey = self::CACHE_PREFIX."booked_appointments_{$date->format('Y_m')}";

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($dateRange) {
                return Appointment::query()
                    ->whereBetween(
                        'date',
                        [$dateRange->values()]
                    )
                    ->whereIn('status', [
                        AppointmentStatus::Confirmed->value,
                        AppointmentStatus::Rescheduled->value,
                    ])
                    ->orderBy('date')
                    ->orderBy('time')
                    ->get()
                    ->groupBy('date')
                    ->map(function (Collection $appointments) {
                        return $appointments
                            ->pluck('time')
                            ->map(
                                fn ($time) => Carbon::parse($time)
                                    ->format('H:i')
                            );
                    });
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

    protected function calculateDateRange(Carbon $date): Collection
    {
        /**
         * We are adding 3 days to the date from which
         * available slots are bookable to give some
         * needed preparation time
         **/
        $startDate = Carbon::today()
            ->startOfDay()
            ->addDays(3);
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
                        true)
            )
            ->mapWithKeys(
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
}
