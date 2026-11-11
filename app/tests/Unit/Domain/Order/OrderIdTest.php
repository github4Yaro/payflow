<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order;

use App\Domain\Order\OrderId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

final class OrderIdTest extends TestCase
{
    public function testInvoicesId(): void
    {
        $uuid = OrderId::generate();
        $this->assertInstanceOf(UuidV7::class, $uuid->id);
    }

    public function testEquals(): void
    {
        $id1 = OrderId::generate();
        $id2 = OrderId::fromString((string) $id1);
        $this->assertTrue($id1->equals($id2));
    }

    public function testNotEquals(): void
    {
        $id1 = OrderId::generate();
        $id2 = OrderId::generate();
        $this->assertFalse($id1->equals($id2));
    }

    public function testCanBeCreatedFromString(): void
    {
        $value = '019c1234-5678-7abc-8def-0123456789df';

        $id = OrderId::fromString($value);

        $this->assertSame($value, $id->toString());
    }

    public function testInvalidUuidIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        OrderId::fromString('not-an-uuid');
    }

    public function testInvoiceIdUuidVersion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $uuidV4 = Uuid::v4();
        OrderId::fromString($uuidV4->toString());
    }
}
