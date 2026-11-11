<?php

declare(strict_types=1);

namespace App\Tests\Integration\Concurrency;

use App\Domain\Invoice\Invoice;
use App\Domain\Invoice\InvoiceId;
use App\Domain\Invoice\InvoiceNumber;
use App\Domain\Invoice\InvoiceSeries;
use App\Domain\Invoice\InvoiceStatus;
use App\Domain\Order\Order;
use App\Domain\Order\OrderId;
use App\Domain\Shared\Currency;
use App\Domain\Shared\Money;
use App\Tests\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;

abstract class ConcurrencyTestCase extends IntegrationTestCase
{
    /**
     * @throws \DateMalformedStringException
     * @throws ORMException
     */
    protected function givenIssuedInvoice(): Invoice
    {
        $order = new Order(OrderId::generate(), new Money(Currency::EUR, 4200));
        $issuedAt = new \DateTimeImmutable('2026-08-19 10:00:00', new \DateTimeZone('UTC'));
        $number = new InvoiceNumber(InvoiceSeries::forYear(2026), 731);

        $invoice = Invoice::issueForOrder($order, $number, $issuedAt);

        $this->em->persist($order);
        $this->em->persist($invoice);
        $this->em->flush();
        $this->em->clear();

        return $invoice;
    }

    protected function currentStatus(InvoiceId $id): ?string
    {
        $loaded = $this->em->find(Invoice::class, $id);

        return $loaded?->status->value;
    }

    /**
     * @throws Exception
     */
    protected function insertInvoice(Connection $conn, Order $order, int $sequence): void
    {
        $conn->executeStatement(
            'INSERT INTO invoices
            (id, order_id, status, number_series, number_sequence,
             total_minor_units, total_currency, issued_at)
         VALUES (:id, :orderId, :status, :series, :sequence, :amount, :currency, :issuedAt)',
            [
                'id' => (string) InvoiceId::generate(),
                'orderId' => (string) $order->id,
                'status' => InvoiceStatus::Issued->value,
                'series' => 2026,
                'sequence' => $sequence,
                'amount' => $order->amount->minorUnits,
                'currency' => $order->amount->currency->value,
                'issuedAt' => '2026-08-24 10:00:00+00',
            ],
        );
    }

    protected function givenPlacedOrder(): Order
    {
        $order = new Order(OrderId::generate(), new Money(Currency::EUR, 4200));
        $this->em->persist($order);
        $this->em->flush();
        $this->em->clear();

        return $order;
    }

    protected function activeInvoiceCount(Connection $conn, OrderId $id): int
    {
        $count = $conn->fetchOne("SELECT count(*) FROM invoices WHERE order_id = ? AND status <> 'void'", [$id]);

        $this->assertIsInt($count);

        return $count;
    }
}
