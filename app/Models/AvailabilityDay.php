<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AvailabilityDay extends Pivot
{
    protected $table = 'availability_day';
}
