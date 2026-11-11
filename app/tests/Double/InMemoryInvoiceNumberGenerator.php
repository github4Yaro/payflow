<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Domain\Invoice\InvoiceNumber;
use App\Domain\Invoice\InvoiceNumberGenerator;
use App\Domain\Invoice\InvoiceSeries;

final class InMemoryInvoiceNumberGenerator implements InvoiceNumberGenerator
{
    /** @var array<string,int> */
    private array $counters = [];

    public function __construct(private readonly int $startFrom = 1)
    {
    }

    #[\Override]
    public function next(InvoiceSeries $series): InvoiceNumber
    {
        $key = (string) $series;
        $this->counters[$key] = ($this->counters[$key] ?? $this->startFrom - 1) + 1;

        return new InvoiceNumber($series, $this->counters[$key]);
    }
}
