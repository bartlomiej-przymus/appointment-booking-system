<?php

namespace App\Models;

use Database\Factories\SlotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Slot extends Model
{
    /** @use HasFactory<SlotFactory> */
    use HasFactory;

    protected $table = 'slots';

    protected $fillable = [
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function availabilities(): BelongsToMany
    {
        return $this->belongsToMany(
            Availability::class,
            AvailabilitySlot::class
        )->using(AvailabilitySlot::class)
            ->withTimestamps();
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class);
    }
}
