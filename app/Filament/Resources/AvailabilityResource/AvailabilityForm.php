<?php

namespace App\Filament\Resources\AvailabilityResource;

use Carbon\Carbon;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\HtmlString;

class AvailabilityForm
{
    public static function make(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Availability Table Name')
                            ->string()
                            ->required(),
                    ]),
                Section::make('Appointment Time Slot Settings')
                    ->schema([
                        TextInput::make('appointment_duration')
                            ->default(30)
                            ->required()
                            ->numeric()
                            ->step(5)
                            ->suffix('min')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set) {
                                $set('time_slots', []);
                            }),
                        TextInput::make('break')
                            ->label('Break Between Appointments')
                            ->default(15)
                            ->required()
                            ->numeric()
                            ->step(5)
                            ->suffix('min')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set) {
                                $set('time_slots', []);
                            }),
                        FieldSet::make('Start Time')
                            ->schema([
                                Select::make('start_h')
                                    ->label('Hour')
                                    ->default(9)
                                    ->options(fn () => array_combine(range(1, 24), array_map('strval', range(1, 24))))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('time_slots', []);
                                    }),
                                Select::make('start_min')
                                    ->label('Minutes')
                                    ->default(0)
                                    ->options([0 => '00', 15 => '15', 30 => '30', 45 => '45'])
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('time_slots', []);
                                    }),
                            ])->columnSpan(1),
                        Fieldset::make('End Time')
                            ->schema([
                                Select::make('end_h')
                                    ->label('Hour')
                                    ->default(17)
                                    ->options(fn () => array_combine(range(1, 24), array_map('strval', range(1, 24))))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('time_slots', []);
                                    }),
                                Select::make('end_min')
                                    ->label('Minutes')
                                    ->default(0)
                                    ->options([0 => '00', 15 => '15', 30 => '30', 45 => '45'])
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('time_slots', []);
                                    }),
                            ])->columnSpan(1),
                    ])->columns(),
                Section::make('Availability Time Slots')
                    ->description(new HtmlString(
                        'If you have chosen daily schedule type you need to add availability time slots to it, using select box below.'
                    ))
                    ->schema([
                        Select::make('time_slots')
                            ->label('Time Slots')
                            ->multiple()
                            ->required()
                            ->options(function (Get $get): array {
                                $startH = $get('start_h');
                                $startM = $get('start_min');
                                $endH = $get('end_h');
                                $endM = $get('end_min');
                                $duration = $get('appointment_duration');
                                $break = $get('break');

                                if (! isset($startH, $startM, $endH, $endM, $duration, $break)) {
                                    return [];
                                }

                                // Format times properly
                                $startTime = sprintf('%02d:%02d', $startH, $startM);
                                $endTime = sprintf('%02d:%02d', $endH, $endM);

                                return static::generateAvailableSlots(
                                    Carbon::createFromTimeString($startTime),
                                    Carbon::createFromTimeString($endTime),
                                    (int) $duration,
                                    (int) $break
                                );
                            })
                            ->afterStateUpdated(function (Set $set, $state) {
                                if (empty($state)) {
                                    $set('time_slots', []);
                                }
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(),
            ]);
    }

    public static function generateAvailableSlots(
        Carbon $startTime,
        Carbon $endTime,
        int $duration,
        int $break
    ): array {
        $availableSlots = [];

        // Add the slot duration and break to get total interval between the start of consecutive appointments
        $interval = $duration + $break;

        while ($startTime->lessThan($endTime)) {
            // Calculate the end of the current slot
            $slotEnd = $startTime->copy()->addMinutes($duration);

            // If the end of the slot is within working hours, add it to the array
            if ($slotEnd->lessThanOrEqualTo($endTime)) {
                $startFormatted = $startTime->format('H:i');
                $endFormatted = $slotEnd->format('H:i');
                $availableSlots[$startFormatted] = "$startFormatted till $endFormatted";
            }

            // Move the start time forward by the total slot interval (duration + break)
            $startTime->addMinutes($interval);
        }

        return $availableSlots;
    }
}
