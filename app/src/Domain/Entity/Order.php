<?php

namespace App\Domain\Entity;

use App\Domain\Enum\OrderStatus;
use App\Domain\VO\Money;
use App\Domain\VO\OrderId;

final class Order
{
    public OrderStatus $status;

    public function __construct(
        public readonly OrderId $id,
        public Money $amount,
    ) {
    }

    public function place(): void
    {
        $this->status = OrderStatus::New;
    }

    public function processing(): void
    {
        $status = OrderStatus::Processing;
        if ($this->status->canSetTo($status)) {
            $this->status = $status;
        }
    }

    public function complete(): void
    {
        $status = OrderStatus::Completed;
        if ($this->status->canSetTo($status)) {
            $this->status = $status;
        }
    }

    public function cancel(): void
    {
        $status = OrderStatus::Canceled;
        if ($this->status->canSetTo($status)) {
            $this->status = $status;
        }
    }
}
