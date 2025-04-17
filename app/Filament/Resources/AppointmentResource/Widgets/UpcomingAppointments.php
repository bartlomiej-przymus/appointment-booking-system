<?php

namespace App\Filament\Resources\AppointmentResource\Widgets;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource\Pages\ListAppointments;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UpcomingAppointments extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListAppointments::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Upcoming Appointments', $this->getPageTableQuery()->where('status', AppointmentStatus::Confirmed->value)->count()),
        ];
    }
}
