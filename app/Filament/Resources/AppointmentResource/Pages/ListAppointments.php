<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Filament\Resources\AppointmentResource\Widgets\UpcomingAppointments;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListAppointments extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = AppointmentResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            UpcomingAppointments::class,
        ];
    }
}
