<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function days(): HasMany
    {
        return $this->hasMany(Day::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
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
