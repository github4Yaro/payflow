<?php

declare(strict_types=1);

namespace App\Tests\Integration\Concurrency;

use Doctrine\DBAL\Exception\DriverException;

final class WriteSkewTest extends ConcurrencyTestCase
{
    public function testWriteSkewIsPreventedByPartialUniqueIndex(): void
    {
        $order = $this->givenPlacedOrder();

        $a = $this->newConnection();
        $b = $this->newConnection();

        foreach ([$a, $b] as $conn) {
            $conn->beginTransaction();
            $conn->executeStatement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            self::assertSame(0, $this->activeInvoiceCount($conn, $order->id));
        }

        $this->insertInvoice($a, $order, sequence: 1);
        $a->commit();

        try {
            $this->insertInvoice($b, $order, sequence: 2);
            $b->commit();
            self::fail('expected the constraint to reject the second invoice');
        } catch (DriverException $e) {
            self::assertContains($e->getSQLState(), ['23505', '40001']);
        }
    }
}
