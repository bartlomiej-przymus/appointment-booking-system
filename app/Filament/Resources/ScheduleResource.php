<?php

namespace App\Filament\Resources;

use App\Enums\DayType;
use App\Filament\Resources\ScheduleResource\Pages\CreateSchedule;
use App\Filament\Resources\ScheduleResource\Pages\EditSchedule;
use App\Filament\Resources\ScheduleResource\Pages\ListSchedules;
use App\Filament\Resources\ScheduleResource\RelationManagers\DaysRelationManager;
use App\Filament\Resources\ScheduleResource\ScheduleForm;
use App\Models\Schedule;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationLabel = 'Schedules';

    protected static ?string $navigationGroup = 'Schedule Settings';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return ScheduleForm::make($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('excluded_days')
                    ->color(fn ($state) => DayType::from($state)->getColor())
                    ->formatStateUsing(fn ($state) => Str::title($state))
                    ->badge()
                    ->separator(),
                IconColumn::make('active')
                    ->state(fn ($record) => $record->isActive())
                    ->boolean(),
                TextColumn::make('active_from')
                    ->date(),
                TextColumn::make('active_to')
                    ->date(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->dateTimeTooltip(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DaysRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSchedules::route('/'),
            'create' => CreateSchedule::route('/create'),
            'edit' => EditSchedule::route('/{record}/edit'),
        ];
    }
}
