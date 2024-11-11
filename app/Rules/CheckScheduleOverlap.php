<?php

namespace App\Rules;

use App\Models\Schedule;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckScheduleOverlap implements ValidationRule
{
    protected ?string $activeFrom;

    public function forActiveFromDate(
        $activeFrom,

    ): self {
        $this->activeFrom = $activeFrom;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->activeFrom) {
            return;
        }

        $hasOverlap = Schedule::query()
            ->whereDate('active_from', '<=', $value)
            ->whereDate('active_to', '>=', $this->activeFrom)
            ->exists();

        if ($hasOverlap) {
            $fail('The Schedule dates overlap with an existing one. Please check for conflicts');
        }
    }
}
