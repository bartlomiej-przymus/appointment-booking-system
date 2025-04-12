<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointments';

    protected $fillable = [
        'user_id',
        'schedule_id',
        'date',
        'time_slot',
        'status',
        'duration', // int in whole minutes
    ];

    protected $casts = [
        'date' => 'datetime',
        'time_slot' => 'datetime:H:i',
        'status' => AppointmentStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function slots(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function isAvailable(string $date, string $timeSlot, int $scheduleId): bool
    {
        return ! $this->where('date', $date)
            ->where('time_slot', $timeSlot)
            ->where('schedule_id', $scheduleId)
            ->whereNotIn('status', [
                AppointmentStatus::Cancelled,
                AppointmentStatus::Rescheduled,
            ])
            ->exists();
    }
}
