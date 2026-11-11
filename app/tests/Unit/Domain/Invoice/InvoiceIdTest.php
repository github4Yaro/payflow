<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Invoice;

use App\Domain\Invoice\InvoiceId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

final class InvoiceIdTest extends TestCase
{
    public function testInvoicesId(): void
    {
        $uuid = InvoiceId::generate();
        $this->assertInstanceOf(UuidV7::class, $uuid->id);
    }

    public function testEquals(): void
    {
        $id1 = InvoiceId::generate();
        $id2 = InvoiceId::fromString((string) $id1);
        $this->assertTrue($id1->equals($id2));
    }

    public function testNotEquals(): void
    {
        $id1 = InvoiceId::generate();
        $id2 = InvoiceId::generate();
        $this->assertFalse($id1->equals($id2));
    }

    public function testCanBeCreatedFromString(): void
    {
        $value = '019c1234-5678-7abc-8def-0123456789ab';

        $id = InvoiceId::fromString($value);

        $this->assertSame($value, $id->toString());
    }

    public function testInvalidUuidIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InvoiceId::fromString('not-an-uuid');
    }

    public function testInvoiceIdUuidVersion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $uuidV4 = Uuid::v4();
        InvoiceId::fromString($uuidV4->toString());
    }
}
