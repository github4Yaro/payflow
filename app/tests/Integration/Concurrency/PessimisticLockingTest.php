<?php

declare(strict_types=1);

namespace App\Tests\Integration\Concurrency;

use Doctrine\DBAL\Exception\DriverException;

final class PessimisticLockingTest extends ConcurrencyTestCase
{
    public function testCreateWithPessimisticLocking(): void
    {
        $order = $this->givenPlacedOrder();

        $a = $this->newConnection();
        $b = $this->newConnection();

        $a->beginTransaction();
        self::assertSame((string) $order->id, $a->fetchOne('SELECT id FROM orders WHERE id = ? FOR UPDATE', [(string) $order->id]));

        $b->beginTransaction();
        try {
            $b->fetchOne('SELECT id FROM orders WHERE id = ? FOR UPDATE', [(string) $order->id]);
            self::fail('second issuer must not proceed while the first holds the order row');
        } catch (DriverException $e) {
            self::assertSame('55P03', $e->getSQLState());   // lock_timeout
        }

        $a->commit();

        $b->rollBack();
        $b->beginTransaction();
        self::assertSame((string) $order->id, $b->fetchOne('SELECT id FROM orders WHERE id = ? FOR UPDATE', [(string) $order->id]));
        $b->rollBack();
    }
}
