<?php

namespace App\Filament\Resources\AvailabilityResource\Pages;

use App\Filament\Resources\AvailabilityResource;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateAvailability extends CreateRecord
{
    protected static string $resource = AvailabilityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (array_key_exists('start_h', $data) && array_key_exists('start_min', $data)) {
            $data['start_time'] = Carbon::now()->setTime($data['start_h'], $data['start_min'])->format('H:i');
        }

        if (array_key_exists('end_h', $data) && array_key_exists('end_min', $data)) {
            $data['end_time'] = Carbon::now()->setTime($data['end_h'], $data['end_min'])->format('H:i');
        }

        return $data;
    }

    public function afterCreate(): void
    {
        $slots = collect($this->data['time_slots'])
            ->map(function ($timeSlot) {
                return [
                    'start_time' => $timeSlot,
                    'end_time' => Carbon::createFromTimeString($timeSlot)
                        ->addMinutes($this->data['appointment_duration'])
                        ->toTimeString('minute'),
                ];
            });

        $this->record->slots()->createMany($slots->all());
    }
}
