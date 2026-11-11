<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Invoice;

use App\Domain\Invoice\InvalidInvoiceStatusException;
use App\Domain\Invoice\Invoice;
use App\Domain\Invoice\InvoiceId;
use App\Domain\Invoice\InvoiceSeries;
use App\Domain\Invoice\InvoiceStatus;
use App\Domain\Order\Order;
use App\Domain\Order\OrderId;
use App\Domain\Shared\Currency;
use App\Domain\Shared\Money;
use App\Tests\Double\InMemoryInvoiceNumberGenerator;
use PHPUnit\Framework\TestCase;

final class InvoiceTest extends TestCase
{
    private Invoice $invoice;

    public function testVoidInvoice(): void
    {
        $invoice = $this->invoice;
        $invoice->void();
        self::assertSame(InvoiceStatus::Void, $invoice->status);
    }

    public function testPayInvoice(): void
    {
        $invoice = $this->invoice;
        $invoice->pay();
        self::assertSame(InvoiceStatus::Paid, $invoice->status);
    }

    public function testRefundInvoice(): void
    {
        $invoice = $this->paidInvoice();
        $invoice->refund();
        self::assertSame(InvoiceStatus::Refunded, $invoice->status);
    }

    public function testOverdueInvoice(): void
    {
        $invoice = $this->invoice;
        $invoice->overdue();
        self::assertSame(InvoiceStatus::Overdue, $invoice->status);
    }

    public function testRefundVoidedInvoice(): void
    {
        $this->expectException(InvalidInvoiceStatusException::class);
        $invoice = $this->voidedInvoice();
        $invoice->refund();
    }

    public function testExceptionWillThrown(): void
    {
        $invoice = $this->voidedInvoice();
        try {
            $invoice->refund();
            self::fail('expected exception');
        } catch (InvalidInvoiceStatusException $e) {
            self::assertSame(InvoiceStatus::Void, $invoice->status);
        }
    }

    public function testCreateInvoiceForOrder(): void
    {
        $orderId = OrderId::generate();
        $amount = new Money(Currency::CAD, 1000);
        $order = new Order($orderId, $amount);
        $invoiceNumberGenerator = new InMemoryInvoiceNumberGenerator(startFrom: 731);
        $issuedAt = new \DateTimeImmutable();
        $invoiceSeries = InvoiceSeries::fromIssueDate($issuedAt);
        $invoiceNumber = $invoiceNumberGenerator->next($invoiceSeries);
        $invoice = Invoice::issueForOrder($order, $invoiceNumber, $issuedAt);
        self::assertSame(InvoiceStatus::Issued, $invoice->status);
        self::assertTrue($order->id->equals($invoice->orderId));
        self::assertTrue($order->amount->equals($invoice->amount));
        self::assertSame('INV-2026-000731', (string) $invoice->number);
    }

    public function testOverdueInvoiceCanStillBePaid(): void
    {
        $invoice = $this->invoice;
        $invoice->overdue();

        $invoice->pay();

        self::assertSame(InvoiceStatus::Paid, $invoice->status);
    }

    #[\Override]
    protected function setUp(): void
    {
        $orderId = OrderId::generate();
        $money = new Money(Currency::USD, 100);
        $id = InvoiceId::generate();

        $invoiceNumberGenerator = new InMemoryInvoiceNumberGenerator();
        $issuedAt = new \DateTimeImmutable();
        $invoiceSeries = InvoiceSeries::fromIssueDate($issuedAt);
        $invoiceNumber = $invoiceNumberGenerator->next($invoiceSeries);

        $invoice = new Invoice(
            id: $id,
            amount: $money,
            orderId: $orderId,
            number: $invoiceNumber,
            issuedAt: $issuedAt
        );

        $this->invoice = $invoice;
    }

    private function paidInvoice(): Invoice
    {
        $invoice = $this->invoice;
        $invoice->pay();

        return $invoice;
    }

    private function voidedInvoice(): Invoice
    {
        $invoice = $this->invoice;
        $invoice->void();

        return $invoice;
    }
}
