<?php

namespace App\Support;

/**
 * Digits in the script of the language being read.
 *
 * Bangla writes its own numerals, so a figure the page itself prints — a date,
 * a time, a counter — should follow the page rather than stay Latin.
 */
class Numerals
{
    private const BANGLA = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
        '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];

    public static function localize(string|int|float|null $value, ?string $locale = null): string
    {
        $value = (string) $value;

        return ($locale ?? app()->getLocale()) === 'bn' ? strtr($value, self::BANGLA) : $value;
    }
}
