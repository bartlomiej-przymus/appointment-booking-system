<?php

namespace App\Services;

use App\Enums\ScheduleType;
use App\Models\Schedule;
use Carbon\Carbon;

class ScheduleService
{
    public function getActiveSchedule(): ?Schedule
    {
        return Schedule::where('active', true)->first()
            ?? Schedule::where('active_from', '<=', now())
                ->where('active_to', '>=', now())
                ->first();
    }

    public function isDateBookable(string $date): bool
    {
        $schedule = $this->getActiveSchedule();

        if ($schedule === null) {
            return false;
        }

        return match ($schedule->type) {
            ScheduleType::Daily => $this->checkDailyDateBookable($date),
            ScheduleType::Weekly => $this->checkWeeklyDateBookable($date),
            ScheduleType::Custom => $this->checkCustomDateBookable($date),
            default => false,
        };
    }

    private function checkDailyDateBookable(Schedule $schedule, string $date): bool
    {
        $excluded_days = $schedule->excluded_days();

        $day = Carbon::createFromTimeString($date)->day;
    }
}
