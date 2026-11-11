<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Invoice;

use App\Domain\Invoice\InvoiceNumber;
use App\Domain\Invoice\InvoiceSeries;
use PHPUnit\Framework\TestCase;

final class InvoiceNumberTest extends TestCase
{
    public function testInvoicesNumberIsCreated(): void
    {
        $invoiceSeries = InvoiceSeries::forYear(2026);
        $invoiceNumber = new InvoiceNumber($invoiceSeries, 1);
        $this->assertSame('INV-2026-000001', (string) $invoiceNumber);
    }

    public function testInvoicesNumberCreationFail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $invoiceSeries = InvoiceSeries::forYear(2026);
        new InvoiceNumber($invoiceSeries, 0);
    }

    public function testInvoicesNumberEquals(): void
    {
        $invoiceSeries = InvoiceSeries::forYear(2026);
        $invoiceNumber1 = new InvoiceNumber($invoiceSeries, 1);
        $invoiceNumber2 = new InvoiceNumber($invoiceSeries, 1);
        $this->assertTrue($invoiceNumber1->equals($invoiceNumber2));
    }

    public function testInvoicesNumberNotEquals(): void
    {
        $invoiceSeries = InvoiceSeries::forYear(2026);
        $invoiceNumber1 = new InvoiceNumber($invoiceSeries, 1);
        $invoiceNumber2 = new InvoiceNumber($invoiceSeries, 2);
        $this->assertFalse($invoiceNumber1->equals($invoiceNumber2));
    }
}
