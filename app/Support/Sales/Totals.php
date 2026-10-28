<?php

namespace App\Support\Sales;

use App\Support\Money;

/**
 * How a proposal's or an order's figures follow from its lines.
 *
 *   line total  = quantity × unit price − line discount (never below zero)
 *   subtotal    = Σ quantity × unit price
 *   discounts   = Σ line discounts + the extra discount on the whole
 *   tax         = tax rate % of (subtotal − discounts)
 *   total       = subtotal − discounts + tax
 *
 * The same sums serve the live preview in the form and the figures saved on
 * the server, which are always recomputed here rather than taken from a form.
 */
class Totals
{
    /** @return array{gross: int, discount: int, total: int} in paisa */
    public static function line(string|int|float|null $quantity, string|int|float|null $unitPrice, string|int|float|null $discount): array
    {
        $gross = Money::multiply(Money::toMinor($unitPrice), $quantity);
        $lineDiscount = min(max(Money::toMinor($discount), 0), $gross);

        return ['gross' => $gross, 'discount' => $lineDiscount, 'total' => $gross - $lineDiscount];
    }

    /**
     * @param  iterable<array{quantity?: mixed, unit_price?: mixed, discount?: mixed}>  $items
     * @return array{subtotal: int, discount_total: int, tax_amount: int, total: int} in paisa
     */
    public static function of(iterable $items, string|int|float|null $extraDiscount, string|int|float|null $taxRate): array
    {
        $subtotal = 0;
        $discounts = 0;

        foreach ($items as $item) {
            $line = self::line($item['quantity'] ?? 0, $item['unit_price'] ?? 0, $item['discount'] ?? 0);
            $subtotal += $line['gross'];
            $discounts += $line['discount'];
        }

        $discounts = min($discounts + max(Money::toMinor($extraDiscount), 0), $subtotal);
        $taxable = $subtotal - $discounts;
        $tax = Money::toMinor($taxRate) > 0 ? Money::percent($taxable, $taxRate) : 0;

        return [
            'subtotal' => $subtotal,
            'discount_total' => $discounts,
            'tax_amount' => $tax,
            'total' => $taxable + $tax,
        ];
    }
}
