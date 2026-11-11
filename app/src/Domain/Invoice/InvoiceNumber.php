<?php

declare(strict_types=1);

namespace App\Domain\Invoice;

final readonly class InvoiceNumber
{
    public function __construct(
        public InvoiceSeries $series,
        public int $sequence,
    ) {
        if ($sequence < 1) {
            throw new \InvalidArgumentException('Sequence must be greater than 0');
        }
    }

    public function __toString(): string
    {
        return sprintf(
            '%s-%06d',
            $this->series,
            $this->sequence
        );
    }

    public function equals(InvoiceNumber $number): bool
    {
        return $this->sequence === $number->sequence && $this->series->equals($number->series);
    }
}
