<?php

namespace App\Rules;

use App\Models\Schedule;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CanSetActive implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value && $this->cannotSetActive()) {
            $fail('Cannot set this Schedule to active as there is already one with active date range');
        }
    }

    public function cannotSetActive(): bool
    {
        return Schedule::whereNotNull('active_from')
            ->orWhereNotNull('active_to')
            ->exists();
    }
}
