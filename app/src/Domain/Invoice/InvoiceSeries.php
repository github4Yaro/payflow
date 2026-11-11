<?php

declare(strict_types=1);

namespace App\Domain\Invoice;

final readonly class InvoiceSeries
{
    private function __construct(public int $year)
    {
    }

    public static function forYear(int $year): self
    {
        if ($year < 2000 || $year > 9999) {
            throw new \InvalidArgumentException('year out of range');
        }

        return new self($year);
    }

    public static function fromIssueDate(\DateTimeImmutable $issuedAt): self
    {
        return self::forYear((int) $issuedAt->format('Y'));
    }

    public function equals(self $other): bool
    {
        return $this->year === $other->year;
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return sprintf('INV-%d', $this->year);
    }
}
