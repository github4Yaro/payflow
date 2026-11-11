<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\invoice;

use App\Domain\Invoice\Invoice;
use App\Domain\Invoice\InvoiceNumber;
use App\Domain\Invoice\InvoiceSeries;
use App\Domain\Order\OrderId;
use App\Infrastructure\Webhook\IncomingWebhook;

trait InvoiceFixtureTrait
{
    private function createInvoiceForWebhook(IncomingWebhook $WebhookPayload): Invoice
    {
        $orderId = OrderId::generate();
        $issuedAt = new \DateTimeImmutable('2026-08-19 10:00:00', new \DateTimeZone('UTC'));
        $number = new InvoiceNumber(InvoiceSeries::forYear(2026), 731);

        return new Invoice($WebhookPayload->invoiceId, $WebhookPayload->amount, $orderId, $number, $issuedAt);
    }
}
