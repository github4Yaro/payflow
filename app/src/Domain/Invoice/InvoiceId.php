<?php

declare(strict_types=1);

namespace App\Domain\Invoice;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

final readonly class InvoiceId
{
    private function __construct(public Uuid $id)
    {
    }

    public static function fromString(string $invoiceId): self
    {
        $uuid = Uuid::fromString($invoiceId);
        if (false === $uuid instanceof UuidV7) {
            throw new \InvalidArgumentException('wrong version of Invoice id');
        }

        return new self($uuid);
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public static function generate(): self
    {
        return new self(Uuid::v7());
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }
}
