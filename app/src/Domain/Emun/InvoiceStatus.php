<?php

namespace App\Domain\Enum;

enum InvoiceStatus: string
{
    case New = 'new';
    case Processing = 'processing';
    case Completed = 'completed';
    case Canceled = 'canceled';

    public function canSetTo(self $new): bool
    {
        return match ($this->value) {
            'new' => 'processing' === $new->value || 'canceled' === $new->value,
            'processing' => 'completed' === $new->value || 'canceled' === $new->value,
            'completed',
            'canceled' => false,
        };
    }
}
