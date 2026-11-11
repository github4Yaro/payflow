<?php

declare(strict_types=1);

namespace App\Tests\Integration\Concurrency;

use Doctrine\DBAL\Exception\DriverException;

final class RepeatableReadTest extends ConcurrencyTestCase
{
    public function testRepeatableReadSeesFrozenSnapshot(): void
    {
        $id = $this->givenIssuedInvoice()->id;

        $a = $this->newConnection();
        $b = $this->newConnection();

        $a->beginTransaction();
        $a->executeStatement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        $a->fetchOne('SELECT status FROM invoices WHERE id = ?', [$id]);

        $b->executeStatement("UPDATE invoices SET status = 'paid' WHERE id = ?", [$id]);

        self::assertSame('issued', $a->fetchOne('SELECT status FROM invoices WHERE id = ?', [$id]));

        $a->rollBack();
    }

    public function testReadCommittedSeesLatestCommittedData(): void
    {
        $id = $this->givenIssuedInvoice()->id;

        $a = $this->newConnection();
        $b = $this->newConnection();

        $a->beginTransaction();
        $a->executeStatement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
        $a->fetchOne('SELECT status FROM invoices WHERE id = ?', [$id]);

        $b->executeStatement("UPDATE invoices SET status = 'paid' WHERE id = ?", [$id]);

        self::assertSame('paid', $a->fetchOne('SELECT status FROM invoices WHERE id = ?', [$id]));

        $a->rollBack();
    }

    public function testConcurrentUpdateFailsWithSerializationError(): void
    {
        $id = $this->givenIssuedInvoice()->id;

        $a = $this->newConnection();
        $b = $this->newConnection();

        $a->beginTransaction();
        $a->executeStatement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        $a->fetchOne('SELECT status FROM invoices WHERE id = ?', [$id]);

        $b->executeStatement("UPDATE invoices SET status = 'paid' WHERE id = ?", [$id]);

        try {
            $a->executeStatement("UPDATE invoices SET status = 'void' WHERE id = ?", [$id]);
            self::fail('expected a serialization failure');
        } catch (DriverException $e) {
            self::assertSame('40001', $e->getSQLState());
        }
    }

    public function testLostUpdateOnRCIsolation(): void
    {
        $id = $this->givenIssuedInvoice()->id;

        $a = $this->newConnection();
        $b = $this->newConnection();

        $a->beginTransaction();
        $a->executeStatement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
        $a->fetchOne('SELECT status FROM invoices WHERE id = ?', [$id]);

        $b->executeStatement("UPDATE invoices SET status = 'paid' WHERE id = ?", [$id]);

        $affected = $a->executeStatement("UPDATE invoices SET status = 'void' WHERE id = ?", [$id]);
        $a->commit();

        self::assertSame(1, $affected);
        self::assertSame('void', $this->currentStatus($id));
    }
}
