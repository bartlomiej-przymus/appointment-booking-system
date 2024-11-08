<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AvailabilitySlot extends Pivot
{
    protected $table = 'availability_slot';
}
