<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Pure calculation rules (no DB, no framework state) so they are trivially unit-testable.
 *
 *   line total  = qty x unit_price            (rounded to 2 dp)
 *   subtotal    = sum of line totals
 *   tax / line  = line total x tax_percent / 100
 *   tax total   = sum of line taxes           (rounded at the end)
 *   grand total = subtotal - discount + tax total
 *
 * Discount is applied to the subtotal and does NOT reduce tax (as in the brief's
 * example). Confirm tax-on-discount handling with the lead before changing this.
 */
final class InvoiceCalculator
{
    /**
     * @param list<array<string, mixed>> $items each with qty, unit_price, tax_percent
     *
     * @return array{items: list<array<string, mixed>>, subtotal: float, discount: float, tax_total: float, grand_total: float}
     */
    public static function calculate(array $items, float $discount = 0.0): array
    {
        $subtotal = 0.0;
        $taxTotal = 0.0;
        $out      = [];

        foreach ($items as $item) {
            $qty   = max(0.0, (float) ($item['qty'] ?? 0));
            $price = max(0.0, (float) ($item['unit_price'] ?? 0));
            $rate  = min(100.0, max(0.0, (float) ($item['tax_percent'] ?? 0)));

            $line      = round($qty * $price, 2);
            $subtotal += $line;
            $taxTotal += $line * $rate / 100;

            $item['qty']         = $qty;
            $item['unit_price']  = $price;
            $item['tax_percent'] = $rate;
            $item['line_total']  = $line;
            $out[]               = $item;
        }

        $subtotal = round($subtotal, 2);
        $discount = round(min(max($discount, 0.0), $subtotal), 2);
        $taxTotal = round($taxTotal, 2);

        return [
            'items'       => $out,
            'subtotal'    => $subtotal,
            'discount'    => $discount,
            'tax_total'   => $taxTotal,
            'grand_total' => round($subtotal - $discount + $taxTotal, 2),
        ];
    }
}
