<?php

declare(strict_types=1);

namespace App\Application\Webhook;

use App\Domain\Invoice\InvoiceId;
use App\Domain\Invoice\InvoiceStatusWriter;

final readonly class MarkInvoicePaid
{
    public function __construct(
        private InvoiceStatusWriter $writer,
    ) {
    }

    public function __invoke(InvoiceId $id): bool
    {
        return $this->writer->markPaid($id);
    }
}
