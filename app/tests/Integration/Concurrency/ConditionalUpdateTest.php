<?php

declare(strict_types=1);

namespace App\Tests\Integration\Concurrency;

use App\Domain\Invoice\InvoiceId;
use App\Infrastructure\Doctrine\Repository\PostgresInvoiceStatusWriter;
use Doctrine\DBAL\Connection;

final class ConditionalUpdateTest extends ConcurrencyTestCase
{
    public function testConditionalUpdateAllowsExactlyOneTransition(): void
    {
        $id = (string) $this->givenIssuedInvoice()->id;

        self::assertSame(true, $this->payIfAllowed($this->newConnection(), $id));
        self::assertSame(false, $this->payIfAllowed($this->newConnection(), $id));
    }

    private function payIfAllowed(Connection $conn, string $id): bool
    {
        $writer = new PostgresInvoiceStatusWriter($conn);
        $invoiceId = InvoiceId::fromString($id);

        return $writer->markPaid($invoiceId);
    }
}
