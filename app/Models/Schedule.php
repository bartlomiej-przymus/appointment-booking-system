<?php

namespace App\Models;

use App\Enums\ScheduleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'schedules';

    protected $fillable = [
        'name',
        'type',
        'excluded_days',
        'active',
        'active_from',
        'active_to',
    ];

    protected function casts(): array
    {
        return [
            'type' => ScheduleType::class,
            'excluded_days' => 'array',
            'active' => 'boolean',
            'active_from' => 'date',
            'active_to' => 'date',
        ];
    }

    public function days(): BelongsToMany
    {
        return $this->belongsToMany(
            Day::class,
            DaySchedule::class
        )->using(DaySchedule::class)
            ->withTimestamps();
    }

    public function availability(): BelongsTo
    {
        return $this->belongsTo(Availability::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        if ($this->active) {
            return true;
        }

        return $this->hasValidDateRange()
            && $this->isWithinActivePeriod()
            && ! $this->hasSchedulesSetToActive();
    }

    public function hasValidDateRange(): bool
    {
        return ! empty($this->active_from) && ! empty($this->active_to);
    }

    public function isWithinActivePeriod(): bool
    {
        return now()->between($this->active_from, $this->active_to);
    }

    public function hasSchedulesSetToActive(): bool
    {
        return Schedule::where('active', true)->exists();
    }
}
