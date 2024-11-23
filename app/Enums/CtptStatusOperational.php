<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class CtptStatusOperational extends Enum
{
    const NotOperational =   false;
    const Operational =   true;

    public static function toEnumArray()
    {
        return [
            self::NotOperational => 'Not Operational',
            self::Operational => 'Operational'
        ];
    }
}
