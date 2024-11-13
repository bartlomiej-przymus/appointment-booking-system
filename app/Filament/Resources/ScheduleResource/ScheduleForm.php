<?php

namespace App\Filament\Resources\ScheduleResource;

use App\Enums\DayType;
use App\Enums\ScheduleType;
use App\Rules\CheckScheduleOverlap;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;

class ScheduleForm
{
    public static function make(Form $form): Form
    {
        return $form
            ->schema([
                Group::make([
                    Section::make('Schedule Name')
                        ->schema([
                            TextInput::make('name')
                                ->hiddenLabel()
                                ->string()
                                ->maxLength(255)
                                ->required(),
                        ]),
                    Section::make('Schedule Type')
                        ->hiddenLabel()
                        ->description(new HtmlString(
                            '<u>Types of schedules:</u><br><br>
                        <b>Daily</b><br>
                        &nbsp<i>Set this type to have same appointment slots repeat daily except excluded days.</i><br><br>
                        <b>Weekly</b><br>
                        &nbsp<i>Set this type to have days with different appointment slots repeat weekly.</i><br><br>
                        <b>Custom</b><br>
                        &nbsp<i>Set this type to choose days and appointment slots for specific date range.</i>'
                        ))
                        ->schema([
                            Select::make('type')
                                ->options(ScheduleType::class)
                                ->disabledOn('edit')
                                ->required()
                                ->live(),
                            Select::make('excluded_days')
                                ->visible(fn (Get $get) => $get('type') === ScheduleType::Daily->value)
                                ->multiple()
                                ->options(DayType::class),
                        ]),
                ])->columnSpan(2),
                Group::make([
                    Section::make('Schedule Settings')
                        ->schema([
                            Toggle::make('active')
                                ->hint('Toggling this makes this Schedule active by default.')
                                ->inline(false)
                                ->live(),
                            DatePicker::make('active_from')
                                ->hidden(fn (Get $get) => $get('active'))
                                ->native(false)
                                ->before('active_to')
                                ->required(fn (Get $get) => ! $get('active'))
                                ->live(),
                            DatePicker::make('active_to')
                                ->hidden(fn (Get $get) => $get('active'))
                                ->native(false)
                                ->after('active_from')
                                ->required(fn (Get $get) => ! $get('active'))
                                ->rules([
                                    fn (Get $get) => (new CheckScheduleOverlap)
                                        ->forActiveFromDate($get('active_from')),
                                ])
                                ->live(),
                        ]),
                    Section::make('Availability Settings')
                        ->visible(fn (Get $get) => $get('type') === ScheduleType::Daily->value)
                        ->schema([
                            Select::make('availability')
                                ->label('Availability Table')
                                ->required()
                                ->relationship('availability', 'name')
                                ->searchable(false)
                                ->preload(),
                        ]),
                ]),
            ])->columns(3);
    }
}
