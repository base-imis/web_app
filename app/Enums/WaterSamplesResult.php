<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class WaterSamplesResult extends Enum
{
    const Positive = 'positive';
    const Negative = 'negative';

    public static function toEnumArray()
    {
        return [
            self::Positive => 'Positive',
            self::Negative => 'Negative',
        ];
    }
}
