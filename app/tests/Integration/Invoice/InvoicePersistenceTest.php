<?php

declare(strict_types=1);

namespace App\Tests\Integration\Invoice;

use App\Domain\Invoice\Invoice;
use App\Domain\Invoice\InvoiceNumber;
use App\Domain\Invoice\InvoiceSeries;
use App\Domain\Invoice\InvoiceStatus;
use App\Domain\Order\Order;
use App\Domain\Order\OrderId;
use App\Domain\Shared\Currency;
use App\Domain\Shared\Money;
use App\Tests\Integration\IntegrationTestCase;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

final class InvoicePersistenceTest extends IntegrationTestCase
{
    /**
     * @throws ORMException
     * @throws \DateMalformedStringException
     * @throws OptimisticLockException
     */
    public function testInvoiceSurvivesRoundTrip(): void
    {
        $order = new Order(OrderId::generate(), new Money(Currency::EUR, 4200));
        $issuedAt = new \DateTimeImmutable('2026-08-19 10:00:00', new \DateTimeZone('UTC'));
        $number = new InvoiceNumber(InvoiceSeries::forYear(2026), 731);

        $invoice = Invoice::issueForOrder($order, $number, $issuedAt);

        $this->em->persist($order);
        $this->em->persist($invoice);
        $this->em->flush();
        $this->em->clear();

        $loaded = $this->em->find(Invoice::class, $invoice->id);

        self::assertNotNull($loaded);
        self::assertNotSame($invoice, $loaded);
        self::assertTrue($invoice->id->equals($loaded->id));
        self::assertTrue($invoice->orderId->equals($loaded->orderId));
        self::assertTrue($invoice->amount->equals($loaded->amount));
        self::assertTrue($invoice->number->equals($loaded->number));
        self::assertSame(InvoiceStatus::Issued, $loaded->status);
        self::assertSame($issuedAt->getTimestamp(), $loaded->issuedAt->getTimestamp());
    }
}
