<?php

declare(strict_types=1);

namespace App\Tests\Integration\Order;

use App\Domain\Order\Order;
use App\Domain\Order\OrderId;
use App\Domain\Order\OrderStatus;
use App\Domain\Shared\Currency;
use App\Domain\Shared\Money;
use App\Tests\Integration\IntegrationTestCase;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

final class OrderPersistenceTest extends IntegrationTestCase
{
    /**
     * @throws OptimisticLockException
     * @throws \DateMalformedStringException
     * @throws ORMException
     */
    public function testOrderSurvivesRoundTrip(): void
    {
        $order = new Order(OrderId::generate(), new Money(Currency::EUR, 4200));

        $this->em->persist($order);
        $this->em->flush();
        $this->em->clear();

        $loaded = $this->em->find(Order::class, $order->id);

        self::assertNotNull($loaded);
        self::assertNotSame($order, $loaded);
        self::assertTrue($order->id->equals($loaded->id));
        self::assertTrue($order->amount->equals($loaded->amount));
        self::assertSame(OrderStatus::Draft, $loaded->status);
    }
}
