<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AvailabilitySchedule extends Pivot
{
    protected $table = 'availability_schedule';
}
