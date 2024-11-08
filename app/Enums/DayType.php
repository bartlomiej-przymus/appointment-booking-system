<?php

namespace App\Enums;

use App\Traits\EnumTrait;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DayType: string implements HasColor, HasLabel
{
    use EnumTrait;

    case Monday = 'monday';
    case Tuesday = 'tuesday';
    case Wednesday = 'wednesday';
    case Thursday = 'thursday';
    case Friday = 'friday';
    case Saturday = 'saturday';
    case Sunday = 'sunday';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Monday => 'Monday',
            self::Tuesday => 'Tuesday',
            self::Wednesday => 'Wednesday',
            self::Thursday => 'Thursday',
            self::Friday => 'Friday',
            self::Saturday => 'Saturday',
            self::Sunday => 'Sunday',
        };
    }

    public function getNumber(): ?int
    {
        return match ($this) {
            self::Monday => 1,
            self::Tuesday => 2,
            self::Wednesday => 3,
            self::Thursday => 4,
            self::Friday => 5,
            self::Saturday => 6,
            self::Sunday => 0,
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::Monday,
            self::Tuesday,
            self::Wednesday,
            self::Thursday,
            self::Friday => 'success',
            self::Saturday,
            self::Sunday => 'danger',
        };
    }
}
