<?php

declare(strict_types=1);

namespace App\Domain\Invoice;

interface InvoiceRepository
{
    /** @throws InvoiceNotFound */
    public function get(InvoiceId $id): Invoice;

    public function save(Invoice $invoice): void;
}
