<?php

namespace App\Enums;

use App\Traits\EnumTrait;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AppointmentStatus: string implements HasColor, HasLabel
{
    use EnumTrait;

    case Pending = 'pending'; //user started booking haven't paid yet
    case Paid = 'paid'; // user paid for appointment needs to confirm in email time and date
    case Cancelled = 'cancelled'; //appointment has not been confirmed and it got cancelled
    case Confirmed = 'confirmed'; //appointment has been confirmed by user
    case Completed = 'completed'; //appointment took place (send feedback form if enabled)
    case Rescheduled = 'rescheduled'; //appointment has been rescheduled

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Cancelled => 'Cancelled',
            self::Confirmed => 'Confirmed',
            self::Completed => 'Completed',
            self::Rescheduled => 'Rescheduled',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Paid => 'success',
            self::Cancelled => 'danger',
            self::Confirmed => 'success',
            self::Completed => 'info',
            self::Rescheduled => 'primary',
        };
    }
}
