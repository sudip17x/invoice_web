<?php

declare(strict_types=1);

use App\Libraries\InvoiceNumber;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class InvoiceNumberTest extends CIUnitTestCase
{
    public function testFirstNumberOfTheYear(): void
    {
        $this->assertSame('INV-2026-0001', InvoiceNumber::next(null, 'INV', 2026));
    }

    public function testIncrementsLastNumber(): void
    {
        $this->assertSame('INV-2026-0002', InvoiceNumber::next('INV-2026-0001', 'INV', 2026));
        $this->assertSame('INV-2026-0100', InvoiceNumber::next('INV-2026-0099', 'INV', 2026));
    }

    public function testGrowsPastFourDigits(): void
    {
        $this->assertSame('INV-2026-10000', InvoiceNumber::next('INV-2026-9999', 'INV', 2026));
    }

    public function testCustomPrefix(): void
    {
        $this->assertSame('ACME-2027-0001', InvoiceNumber::next(null, 'ACME', 2027));
    }

    public function testMalformedLastNumberRestartsSequence(): void
    {
        $this->assertSame('INV-2026-0001', InvoiceNumber::next('garbage', 'INV', 2026));
    }

    public function testLikePatternMatchesPrefixAndYear(): void
    {
        $this->assertSame('INV-2026-%', InvoiceNumber::likePattern('INV', 2026));
    }
}
