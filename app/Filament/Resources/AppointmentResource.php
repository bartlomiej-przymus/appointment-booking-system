<?php

namespace App\Filament\Resources;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource\Pages\ListAppointments;
use App\Filament\Resources\AppointmentResource\Widgets\UpcomingAppointments;
use App\Models\Appointment;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Livewire\Component;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationLabel = 'Appointments';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('showUpcomingOnly')
                    ->label('Show Upcoming Appointments Only')
                    ->icon('heroicon-o-calendar-days')
                    ->action(function (Component $livewire): void {
                        $livewire->tableFilters['status']['values'][0] = AppointmentStatus::Confirmed->value;
                        $livewire->tableFilters['status']['values'][1] = AppointmentStatus::Rescheduled->value;
                    })
                    ->color('success'),
            ])
            ->columns([
                TextColumn::make('user.name')
                    ->label('Client Name'),
                TextColumn::make('date')
                    ->date('Y-m-d'),
                TextColumn::make('time_slot')
                    ->label('Time')
                    ->time('H:i'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('duration')
                    ->numeric()
                    ->suffix('min'),
            ])
            ->actions([
                // TODO: action to reschedule appointment
                // TODO: action to change state of appointment
            ])
            ->filters([
                SelectFilter::make('status')
                    ->multiple()
                    ->options(AppointmentStatus::class),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->bulkActions([
                BulkActionGroup::make([
                    // TODO: action to bulk reschedule appointments
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppointments::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            UpcomingAppointments::class,
        ];
    }
}
