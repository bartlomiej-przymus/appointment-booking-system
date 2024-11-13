<?php

namespace App\Filament\Resources\ScheduleResource\RelationManagers;

use App\Enums\DayType;
use App\Enums\ScheduleType;
use App\Models\Availability;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class DaysRelationManager extends RelationManager
{
    protected static string $relationship = 'days';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->type !== ScheduleType::Daily;
    }

    public function form(Form $form): Form
    {
        $ownerRecord = $this->getOwnerRecord();

        return $form
            ->schema([
                Select::make('type')
                    ->visible($ownerRecord->type->is(ScheduleType::Weekly))
                    ->required()
                    ->options(DayType::class),
                DatePicker::make('date')
                    ->visible($ownerRecord->type->is(ScheduleType::Custom))
                    ->native(false)
                    ->required()
                    ->native(false)
                    ->minDate($ownerRecord->active_from)
                    ->maxDate($ownerRecord->active_to)
                    ->format('d-m-Y'),
                Select::make('availability')
                    ->label('Availability Table')
                    ->required()
                    ->relationship('availability', 'name')
                    ->searchable(false)
                    ->preload()
                    ->default([])
                    ->live(),
                Placeholder::make('Availability Slots')
                    ->hidden(fn (Get $get) => empty($get('availability')))
                    ->content(function (Get $get) {
                        $availabilityTable = Availability::where('id', $get('availability'))->first();

                        $slots = $availabilityTable?->slots
                            ->pluck('start_time')
                            ->map(fn ($time) => $time->toTimeString('minute'))
                            ->toArray();

                        return new HtmlString(implode(', ', $slots));
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        $ownerRecord = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('type')
                    ->visible($ownerRecord->type->is(ScheduleType::Weekly)),
                TextColumn::make('availability.slots')
                    ->label('Available Time Slots')
                    ->formatStateUsing(
                        fn ($state) => $state->start_time->toTimeString('minute')
                    )
                    ->badge(),
                TextColumn::make('date')
                    ->visible($ownerRecord->type->is(ScheduleType::Custom))
                    ->date('l, d F Y'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->createAnother(false),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
