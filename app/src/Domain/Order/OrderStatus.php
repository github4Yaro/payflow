<?php

declare(strict_types=1);

namespace App\Domain\Order;

use App\Domain\Shared\StatusTransitions;

enum OrderStatus: string implements StatusTransitions
{
    case Draft = 'draft';
    case New = 'new';
    case Processing = 'processing';
    case Completed = 'completed';
    case Canceled = 'canceled';
    case Disputed = 'disputed';

    #[\Override]
    public function canSetTo(StatusTransitions $status): bool
    {
        if (!$status instanceof self) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s', self::class, $status::class));
        }

        return match ($this) {
            self::Draft => self::New === $status,
            self::New => self::Processing === $status || self::Canceled === $status,
            self::Processing => self::Completed === $status || self::Disputed === $status || self::Canceled === $status,
            self::Disputed => self::Completed === $status || self::Canceled === $status,
            self::Completed,
            self::Canceled => false,
        };
    }
}
