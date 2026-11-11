<?php

declare(strict_types=1);

namespace App\Domain\Invoice;

use App\Domain\Shared\StatusTransitions;

enum InvoiceStatus: string implements StatusTransitions
{
    case Issued = 'issued';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Void = 'void';
    case Refunded = 'refunded';

    #[\Override]
    public function canSetTo(StatusTransitions $status): bool
    {
        if (!$status instanceof self) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s', self::class, $status::class));
        }

        return match ($this) {
            self::Issued => self::Paid === $status || self::Void === $status || self::Overdue === $status,
            self::Paid => self::Refunded === $status,
            self::Overdue => self::Void === $status || self::Paid === $status,
            self::Void,
            self::Refunded => false,
        };
    }

    /**
     * @return list<self>
     */
    public static function allowedFrom(self $to): array
    {
        return array_values(
            array_filter(
                self::cases(),
                static fn (self $from): bool => $from->canSetTo($to),
            ),
        );
    }
}
