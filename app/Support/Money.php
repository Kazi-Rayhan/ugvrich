<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Exact money arithmetic for the sales records.
 *
 * Amounts are stored as decimal(12,2) and handled here as whole paisa (int),
 * parsed from their string form, so no sum ever passes through a float.
 */
class Money
{
    public const CURRENCY = 'BDT';

    public const SYMBOL = '৳';

    /** "1,234.5" / "1234.50" / 1234 → 123450. Rounds half up past two decimals. */
    public static function toMinor(string|int|float|null $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_float($value)) {
            // Only reached from code that already has a float; keep it exact to the cent.
            $value = number_format($value, 2, '.', '');
        }

        $value = str_replace([',', ' ', self::SYMBOL], '', trim((string) $value));

        if (! preg_match('/^(-)?(\d*)(?:\.(\d*))?$/', $value, $m) || ($m[2] === '' && ($m[3] ?? '') === '')) {
            throw new InvalidArgumentException("Not an amount: {$value}");
        }

        $negative = $m[1] === '-';
        $whole = (int) ($m[2] === '' ? '0' : $m[2]);
        $fraction = str_pad($m[3] ?? '', 3, '0');
        $minor = $whole * 100 + (int) substr($fraction, 0, 2) + ((int) $fraction[2] >= 5 ? 1 : 0);

        return $negative ? -$minor : $minor;
    }

    /** 123450 → "1234.50", the form the database columns take. */
    public static function fromMinor(int $minor): string
    {
        $sign = $minor < 0 ? '-' : '';
        $minor = abs($minor);

        return $sign.intdiv($minor, 100).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }

    /** For people: "৳ 1,234.50". */
    public static function format(string|int|null $amount, bool $isMinor = false): string
    {
        $minor = $isMinor ? (int) $amount : self::toMinor($amount);
        $sign = $minor < 0 ? '-' : '';
        $minor = abs($minor);

        return $sign.self::SYMBOL.' '.number_format(intdiv($minor, 100)).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }

    /** quantity × unit price, both exact to two decimals, rounded half up to the paisa. */
    public static function multiply(int $unitMinor, string|int|float|null $quantity): int
    {
        $hundredths = self::toMinor($quantity);   // the quantity, in hundredths

        return self::divideRounded($unitMinor * $hundredths, 100);
    }

    /** A percentage (e.g. "7.5") of an amount, rounded half up to the paisa. */
    public static function percent(int $minor, string|int|float|null $rate): int
    {
        $rateHundredths = self::toMinor($rate);   // 7.5% → 750

        return self::divideRounded($minor * $rateHundredths, 10000);
    }

    protected static function divideRounded(int $numerator, int $denominator): int
    {
        $sign = $numerator < 0 ? -1 : 1;

        return $sign * intdiv(abs($numerator) * 2 + $denominator, $denominator * 2);
    }
}
