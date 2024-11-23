<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class HotspotDisease extends Enum
{

    const Cholera = 1;
    const Diarrhea = 2;
    const Dysentery = 3;
    const HepatitisA = 4;
    const Polio = 5;
    const Typhoid  =  6;

    public static function toEnumArray()
{
    return [
        self::Cholera => self::getDescription(self::Cholera),
        self::Diarrhea => self::getDescription(self::Diarrhea),
        self::Dysentery => self::getDescription(self::Dysentery),
        self::HepatitisA => ucwords(self::getDescription(self::HepatitisA)),

        self::Polio => self::getDescription(self::Polio),
        self::Typhoid => self::getDescription(self::Typhoid),
    ];
}
}
