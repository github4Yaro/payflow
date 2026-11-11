<?php

declare(strict_types=1);

namespace App\Domain\Invoice;

interface InvoiceStatusWriter
{
    public function markPaid(InvoiceId $id): bool;
}
