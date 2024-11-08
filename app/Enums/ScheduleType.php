<?php

namespace App\Enums;

use App\Traits\EnumTrait;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ScheduleType: string implements HasColor, HasLabel
{
    use EnumTrait;

    case Daily = 'daily';
    case Weekly = 'weekly';
    case Custom = 'custom';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Daily => 'Daily',
            self::Weekly => 'Weekly',
            self::Custom => 'Custom',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Daily => 'warning',
            self::Weekly => 'info',
            self::Custom => 'success',
            //            self::Other => 'danger',
        };
    }
}
