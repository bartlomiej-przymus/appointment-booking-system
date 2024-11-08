<?php

namespace App\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class Time implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        return Carbon::parse($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof Carbon) {
            return $value->toTimeString('minute');
        }

        return Carbon::parse($value)->format('H:i');
    }
}
