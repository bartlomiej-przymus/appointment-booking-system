<?php

namespace App\Models;

use App\Enums\DayType;
use Database\Factories\DayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Day extends Model
{
    /** @use HasFactory<DayFactory> */
    use HasFactory;

    protected $table = 'days';

    protected $fillable = [
        'date',
        'type',
    ];

    protected $casts = [
        'type' => DayType::class,
        'date' => 'date',
    ];

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(
            Schedule::class,
            DaySchedule::class
        )->using(DaySchedule::class)
            ->withTimestamps();
    }

    public function availability(): BelongsTo
    {
        return $this->belongsTo(Availability::class);
    }
}
