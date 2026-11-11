<?php

declare(strict_types=1);

namespace App\Infrastructure\Webhook;

use App\Domain\Invoice\InvoiceId;
use App\Domain\Shared\Money;

final readonly class IncomingWebhook
{
    public function __construct(
        public string $provider,
        public string $eventId,
        public string $eventType,
        public InvoiceId $invoiceId,
        public Money $amount,
        public string $rawBody,
    ) {
    }

    public function payloadHash(): string
    {
        return hash('sha256', $this->rawBody);
    }
}
