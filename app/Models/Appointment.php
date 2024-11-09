<?php

namespace App\Models;

use App\Casts\Time;
use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointments';

    protected $fillable = [
        'date',
        'time',
        'status',
        'duration',
    ];

    protected $casts = [
        'date' => 'datetime',
        'time' => Time::class,
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
}
