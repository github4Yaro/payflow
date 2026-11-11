<?php

declare(strict_types=1);

namespace App\Domain\Invoice;

interface InvoiceNumberGenerator
{
    public function next(InvoiceSeries $series): InvoiceNumber;
}
