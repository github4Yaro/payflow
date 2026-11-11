<?php

declare(strict_types=1);

namespace App\Domain\Invoice;

use App\Domain\Order\Order;
use App\Domain\Order\OrderId;
use App\Domain\Shared\Money;

final class Invoice
{
    public private(set) InvoiceStatus $status;

    public function __construct(
        public readonly InvoiceId $id,
        public readonly Money $amount,
        public readonly OrderId $orderId,
        public readonly InvoiceNumber $number,
        public readonly \DateTimeImmutable $issuedAt,
    ) {
        $this->status = InvoiceStatus::Issued;
    }

    public function pay(): void
    {
        $this->transitionTo(InvoiceStatus::Paid);
    }

    public function refund(): void
    {
        $this->transitionTo(InvoiceStatus::Refunded);
    }

    public function void(): void
    {
        $this->transitionTo(InvoiceStatus::Void);
    }

    public function overdue(): void
    {
        $this->transitionTo(InvoiceStatus::Overdue);
    }

    private function transitionTo(InvoiceStatus $to): void
    {
        if ($this->status->canSetTo($to)) {
            $this->status = $to;
        } else {
            throw new InvalidInvoiceStatusException(sprintf('cannot transition from %s to %s', $this->status->value, $to->value));
        }
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }

    public static function issueForOrder(Order $order, InvoiceNumber $invoiceNumber, \DateTimeImmutable $issuedAt): self
    {
        return new self(
            id: InvoiceId::generate(),
            amount: $order->amount,
            orderId: $order->id,
            number: $invoiceNumber,
            issuedAt: $issuedAt
        );
    }
}
