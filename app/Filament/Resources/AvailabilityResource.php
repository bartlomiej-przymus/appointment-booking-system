<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AvailabilityResource\AvailabilityForm;
use App\Filament\Resources\AvailabilityResource\Pages\CreateAvailability;
use App\Filament\Resources\AvailabilityResource\Pages\EditAvailability;
use App\Filament\Resources\AvailabilityResource\Pages\ListAvailabilities;
use App\Models\Availability;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AvailabilityResource extends Resource
{
    protected static ?string $model = Availability::class;

    protected static ?string $modelLabel = 'Availability Table';

    protected static ?string $recordTitleAttribute = 'Availability Timetable';

    protected static ?string $navigationLabel = 'Availability Timetables';

    protected static ?string $navigationGroup = 'Schedule Settings';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return AvailabilityForm::make($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
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

    public static function getPages(): array
    {
        return [
            'index' => ListAvailabilities::route('/'),
            'create' => CreateAvailability::route('/create'),
            'edit' => EditAvailability::route('/{record}/edit'),
        ];
    }
}
