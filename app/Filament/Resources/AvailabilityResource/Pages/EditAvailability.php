<?php

namespace App\Filament\Resources\AvailabilityResource\Pages;

use App\Filament\Resources\AvailabilityResource;
use App\Models\Slot;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditAvailability extends EditRecord
{
    protected static string $resource = AvailabilityResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        parent::mutateFormDataBeforeFill($data);

        $startTime = $this->getRecord()->start_time;
        $endTime = $this->getRecord()->end_time;

        $data['start_h'] = Carbon::parse($startTime)->format('G');
        $data['start_min'] = (int) Carbon::parse($startTime)->format('i');
        $data['end_h'] = Carbon::parse($endTime)->format('G');
        $data['end_min'] = (int) Carbon::parse($endTime)->format('i');
        $data['time_slots'] = $this->getRecord()->slots
            ->pluck('start_time')
            ->map(fn ($time) => $time->toTimeString('minute'))
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (array_key_exists('start_h', $data) && array_key_exists('start_min', $data)) {
            $data['start_time'] = Carbon::now()->setTime($data['start_h'], $data['start_min'])->toTimeString('minute');
        }

        if (array_key_exists('end_h', $data) && array_key_exists('end_min', $data)) {
            $data['end_time'] = Carbon::now()->setTime($data['end_h'], $data['end_min'])->toTimeString('minute');
        }

        return $data;
    }

    public function handleRecordUpdate(Model $record, array $data): Model
    {
        $record = parent::handleRecordUpdate($record, $data);

        $slotIdsToSync = collect($data['time_slots'])
            ->map(function ($startTime) use ($data) {
                $endTime = Carbon::createFromTimeString($startTime)
                    ->addMinutes($data['appointment_duration'])
                    ->toTimeString('minute');

                return Slot::firstOrCreate(
                    ['start_time' => $startTime, 'end_time' => $endTime]
                )->getKey();
            });

        $record->slots()->sync($slotIdsToSync->all());

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
