<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Domain\Invoice\InvoiceId;
use App\Domain\Invoice\InvoiceStatusWriter;

final readonly class InMemoryInvoiceStatusWriter implements InvoiceStatusWriter
{
    public function markPaid(InvoiceId $id): bool
    {
        return true;
    }
}
