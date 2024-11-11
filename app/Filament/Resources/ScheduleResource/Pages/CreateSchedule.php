<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use App\Models\Schedule;
use Filament\Resources\Pages\CreateRecord;

class CreateSchedule extends CreateRecord
{
    protected static string $resource = ScheduleResource::class;

    public function beforeCreate(): void
    {
        if ($this->data['active']) {
            Schedule::where('active', true)
                ->update(['active' => false]);
        }
    }
}
