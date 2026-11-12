<?php

declare(strict_types=1);

use App\Libraries\InvoiceCalculator;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class InvoiceCalculatorTest extends CIUnitTestCase
{
    public function testExampleFromTheBrief(): void
    {
        // 2 x 500.00 @ 18% tax, discount 100.00 => 1000.00 / 180.00 / 1080.00
        $r = InvoiceCalculator::calculate([
            ['qty' => 2, 'unit_price' => 500, 'tax_percent' => 18],
        ], 100.0);

        $this->assertSame(1000.0, $r['subtotal']);
        $this->assertSame(100.0, $r['discount']);
        $this->assertSame(180.0, $r['tax_total']);
        $this->assertSame(1080.0, $r['grand_total']);
        $this->assertSame(1000.0, $r['items'][0]['line_total']);
    }

    public function testMixedTaxRatesAreSummedPerLine(): void
    {
        $r = InvoiceCalculator::calculate([
            ['qty' => 1, 'unit_price' => 100, 'tax_percent' => 18],
            ['qty' => 3, 'unit_price' => 50, 'tax_percent' => 5],
            ['qty' => 2, 'unit_price' => 10, 'tax_percent' => 0],
        ]);

        $this->assertSame(270.0, $r['subtotal']);
        $this->assertSame(25.5, $r['tax_total']); // 18 + 7.5 + 0
        $this->assertSame(295.5, $r['grand_total']);
    }

    public function testRoundsToTwoDecimalsAtTheEnd(): void
    {
        $r = InvoiceCalculator::calculate([
            ['qty' => 3, 'unit_price' => 3.33, 'tax_percent' => 18],
        ]);

        $this->assertSame(9.99, $r['subtotal']);
        $this->assertSame(1.8, $r['tax_total']);     // 1.7982
        $this->assertSame(11.79, $r['grand_total']);
    }

    public function testDiscountCannotExceedSubtotalOrBeNegative(): void
    {
        $items = [['qty' => 1, 'unit_price' => 100, 'tax_percent' => 0]];

        $this->assertSame(100.0, InvoiceCalculator::calculate($items, 999.0)['discount']);
        $this->assertSame(0.0, InvoiceCalculator::calculate($items, 999.0)['grand_total']);
        $this->assertSame(0.0, InvoiceCalculator::calculate($items, -50.0)['discount']);
    }

    public function testBadInputIsClamped(): void
    {
        $r = InvoiceCalculator::calculate([
            ['qty' => -2, 'unit_price' => 10, 'tax_percent' => 500],
            ['qty' => 'abc', 'unit_price' => '5'],
        ]);

        $this->assertSame(0.0, $r['subtotal']);
        $this->assertSame(0.0, $r['grand_total']);
    }

    public function testEmptyInvoiceIsZero(): void
    {
        $r = InvoiceCalculator::calculate([]);

        $this->assertSame(0.0, $r['grand_total']);
        $this->assertSame([], $r['items']);
    }
}
