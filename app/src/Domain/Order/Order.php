<?php

declare(strict_types=1);

namespace App\Domain\Order;

use App\Domain\Shared\Money;

final class Order
{
    public private(set) OrderStatus $status;
    public private(set) \DateTimeImmutable $createdAt;

    public function __construct(
        public readonly OrderId $id,
        public readonly Money $amount,
    ) {
        $this->status = OrderStatus::Draft;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function place(): void
    {
        $this->transitionTo(OrderStatus::New);
    }

    public function processing(): void
    {
        $this->transitionTo(OrderStatus::Processing);
    }

    public function complete(): void
    {
        $this->transitionTo(OrderStatus::Completed);
    }

    public function cancel(): void
    {
        $this->transitionTo(OrderStatus::Canceled);
    }

    private function transitionTo(OrderStatus $to): void
    {
        if ($this->status->canSetTo($to)) {
            $this->status = $to;
        } else {
            throw new InvalidOrderStatusException(sprintf('cannot transition from %s to %s', $this->status->value, $to->value));
        }
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }
}
