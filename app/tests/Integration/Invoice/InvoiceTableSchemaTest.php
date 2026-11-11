<?php

declare(strict_types=1);

namespace App\Tests\Integration\Invoice;

use App\Tests\Integration\IntegrationTestCase;

final class InvoiceTableSchemaTest extends IntegrationTestCase
{
    public function testOfIdxInvoicesUnpaidCover(): void
    {
        $conn = $this->newConnection();

        $def = $conn->fetchOne(
            "SELECT indexdef FROM pg_indexes WHERE indexname = 'idx_invoices_unpaid_cover'"
        );
        self::assertIsString($def, 'index idx_invoices_unpaid_cover is missed');

        self::assertStringContainsString('INCLUDE (total_minor_units)', $def);
        self::assertStringContainsString('issued_at DESC', $def);
        self::assertStringContainsString('WHERE', $def);
    }
}
