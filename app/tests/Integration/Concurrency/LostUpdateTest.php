<?php

declare(strict_types=1);

namespace App\Tests\Integration\Concurrency;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;

final class LostUpdateTest extends ConcurrencyTestCase
{
    /**
     * @throws ORMException
     * @throws Exception
     */
    public function testStaleWriterOverwritesCommittedChange(): void
    {
        $invoice = $this->givenIssuedInvoice();
        $id = $invoice->id;

        $a = $this->newConnection();
        $b = $this->newConnection();

        $a->beginTransaction();
        $seenByA = $a->fetchOne('SELECT status FROM invoices WHERE id = ?', [$id]);

        $b->beginTransaction();
        $seenByB = $b->fetchOne('SELECT status FROM invoices WHERE id = ?', [$id]);

        self::assertSame('issued', $seenByA);
        self::assertSame('issued', $seenByB);

        $b->executeStatement("UPDATE invoices SET status = 'paid' WHERE id = ?", [$id]);
        $b->commit();

        $a->executeStatement("UPDATE invoices SET status = 'void' WHERE id = ?", [$id]);
        $a->commit();

        self::assertSame('void', $this->currentStatus($id));   // 'paid' is gone, no any errors
    }
}
