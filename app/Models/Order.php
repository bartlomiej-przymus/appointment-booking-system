<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'number',
        'amount',
        'status',
        'currency',
        'transaction_id',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    //    Keeping this as an example of dynamic setter and getter for now
    //    protected function total(): Attribute
    //    {
    //        return Attribute::make(
    //            get: fn ($value): int => $value / 100,
    //            set: fn ($value): int => $value * 100
    //        );
    //    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->number = 'MRFLC-'.now()->format('Y-m-d').'-'.$this->getKey() + 1;
        });
    }
}
