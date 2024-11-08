<?php

namespace App\Enums;

use App\Traits\EnumTrait;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasLabel
{
    use EnumTrait;

    case Pending = 'pending';
    case Paid = 'paid';
    case Hold = 'hold';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Hold => 'On Hold',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Hold => 'warning',
            self::Pending => 'info',
            self::Paid => 'success',
            //            self::Other => 'danger',
        };
    }
}
