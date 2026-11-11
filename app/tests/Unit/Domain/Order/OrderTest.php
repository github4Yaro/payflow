<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order;

use App\Domain\Order\InvalidOrderStatusException;
use App\Domain\Order\Order;
use App\Domain\Order\OrderId;
use App\Domain\Order\OrderStatus;
use App\Domain\Shared\Currency;
use App\Domain\Shared\Money;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    private Order $order;

    public function testCreateOrder(): void
    {
        self::assertSame(OrderStatus::Draft, $this->order->status);
    }

    public function testPlaceOrder(): void
    {
        $this->order->place();
        self::assertSame(OrderStatus::New, $this->order->status);
    }

    public function testProcessingOrder(): void
    {
        $order = $this->placedOrder();
        $order->processing();
        self::assertSame(OrderStatus::Processing, $order->status);
    }

    public function testCompleteOrder(): void
    {
        $order = $this->processedOrder();
        $order->complete();
        self::assertSame(OrderStatus::Completed, $order->status);
    }

    public function testCancelOrder(): void
    {
        $order = $this->placedOrder();
        $order->cancel();
        self::assertSame(OrderStatus::Canceled, $order->status);
    }

    public function testCancelledProceed(): void
    {
        $this->expectException(InvalidOrderStatusException::class);
        $order = $this->canceledOrder();
        $order->place();
    }

    public function testExceptionWillThrown(): void
    {
        $order = $this->completedOrder();
        try {
            $order->place();
            self::fail('expected exception');
        } catch (InvalidOrderStatusException $e) {
            self::assertSame(OrderStatus::Completed, $order->status);
        }
    }

    public function testOrdersWithSameIdAreEqualRegardlessOfState(): void
    {
        $order1 = $this->placedOrder();
        $order1Id = (string) $order1->id;
        $order2Id = OrderId::fromString($order1Id);
        $money = new Money(Currency::USD, 300);
        $order2 = new Order(id: $order2Id, amount: $money);
        self::assertTrue($order1->equals($order2));
    }

    public function testIfOrdersNotEqual(): void
    {
        $order1 = $this->order;
        $order2Id = OrderId::generate();
        $order2 = new Order(id: $order2Id, amount: $order1->amount);
        self::assertFalse($order1->equals($order2));
    }

    #[\Override]
    protected function setUp(): void
    {
        $orderId = OrderId::generate();
        $money = new Money(Currency::USD, 100);
        $order = new Order(id: $orderId, amount: $money);
        $this->order = $order;
    }

    private function placedOrder(): Order
    {
        $this->order->place();

        return $this->order;
    }

    private function processedOrder(): Order
    {
        $order = $this->placedOrder();
        $order->processing();

        return $order;
    }

    private function completedOrder(): Order
    {
        $order = $this->processedOrder();
        $order->complete();

        return $order;
    }

    private function canceledOrder(): Order
    {
        $order = $this->placedOrder();
        $order->cancel();

        return $order;
    }
}
