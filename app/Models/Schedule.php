<?php

namespace App\Models;

use App\Enums\ScheduleType;
use Database\Factories\ScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Schedule extends Model
{
    /** @use HasFactory<ScheduleFactory> */
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

    public function availabilities(): BelongsToMany
    {
        return $this->belongsToMany(
            Availability::class,
            AvailabilitySchedule::class
        )->using(AvailabilitySchedule::class)
            ->withTimestamps();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
