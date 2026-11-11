<?php

declare(strict_types=1);

namespace App\Infrastructure\Webhook;

enum WebhookEventStatus: string
{
    case Pending = 'pending';
    case Processed = 'processed';
    case Failed = 'failed';
}
