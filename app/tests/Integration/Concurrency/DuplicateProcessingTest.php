<?php

declare(strict_types=1);

namespace App\Tests\Integration\Concurrency;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;

final class DuplicateProcessingTest extends ConcurrencyTestCase
{
    /**
     * @throws ORMException
     * @throws Exception
     */
    public function testConcurrentHandlersBothPassThePrecondition(): void
    {
        $invoice = $this->givenIssuedInvoice();

        $a = $this->newConnection();
        $b = $this->newConnection();

        $a->beginTransaction();
        $seenByA = $a->fetchOne('SELECT status FROM invoices WHERE id = ?', [$invoice->id]);

        $b->beginTransaction();
        $seenByB = $b->fetchOne('SELECT status FROM invoices WHERE id = ?', [$invoice->id]);

        self::assertSame('issued', $seenByA);
        self::assertSame('issued', $seenByB);

        $b->executeStatement("UPDATE invoices SET status = 'paid' WHERE id = ?", [$invoice->id]);
        $b->commit();

        $a->executeStatement("UPDATE invoices SET status = 'paid' WHERE id = ?", [$invoice->id]);
        $a->commit();

        self::assertSame('paid', $this->currentStatus($invoice->id)); // All is ok, data correct
    }
}
