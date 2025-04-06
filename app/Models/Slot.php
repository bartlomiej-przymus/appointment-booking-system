<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Slot extends Model
{
    use HasFactory;

    protected $table = 'slots';

    protected $fillable = [
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function availabilities(): BelongsToMany
    {
        return $this->belongsToMany(
            Availability::class,
            AvailabilitySlot::class
        )->using(AvailabilitySlot::class)
            ->withTimestamps();
    }
}
