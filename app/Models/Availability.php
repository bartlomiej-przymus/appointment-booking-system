<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Availability extends Model
{
    use HasFactory;

    protected $table = 'availabilities';

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'appointment_duration',
        'break',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function days(): BelongsToMany
    {
        return $this->belongsToMany(
            Day::class,
            AvailabilityDay::class
        )->using(AvailabilityDay::class)
            ->withTimestamps();
    }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(
            Schedule::class,
            AvailabilitySchedule::class
        )->using(AvailabilitySchedule::class)
            ->withTimestamps();
    }

    public function slots(): BelongsToMany
    {
        return $this->belongsToMany(
            Slot::class,
            AvailabilitySlot::class
        )->using(AvailabilitySlot::class)
            ->withTimestamps();
    }
}
