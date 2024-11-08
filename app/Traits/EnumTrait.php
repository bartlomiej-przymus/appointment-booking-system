<?php

namespace App\Traits;

use BackedEnum;

trait EnumTrait
{
    public function is(
        BackedEnum $value
    ): bool {
        return $this === $value;
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function array(): array
    {
        return array_combine(self::names(), self::values());
    }

    public static function valuesArray(): array
    {
        return array_combine(self::values(), self::values());
    }
}
