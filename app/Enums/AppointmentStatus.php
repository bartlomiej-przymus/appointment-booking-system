<?php

namespace App\Enums;

use App\Traits\EnumTrait;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AppointmentStatus: string implements HasColor, HasLabel
{
    use EnumTrait;

    case Pending = 'pending'; // user started booking haven't paid yet
    case Paid = 'paid'; // the user paid for appointment needs to confirm in email time and date
    case Canceled = 'canceled'; // the appointment has not been confirmed and it got canceled
    case Confirmed = 'confirmed'; // user has confirmed appointment
    case Completed = 'completed'; // appointment took place (send a feedback form if enabled)
    case Rescheduled = 'rescheduled'; // the appointment has been rescheduled

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Canceled => 'Canceled',
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
            self::Canceled => 'danger',
            self::Confirmed => 'success',
            self::Completed => 'info',
            self::Rescheduled => 'primary',
        };
    }
}
