<?php

declare(strict_types=1);

namespace App\Infrastructure\Webhook;

use Symfony\Component\HttpFoundation\Response;

enum DeliveryOutcome
{
    case Accepted;
    case Duplicate;
    case Conflict;

    public function statusCode(): int
    {
        return match ($this) {
            self::Accepted, self::Duplicate => Response::HTTP_ACCEPTED,
            self::Conflict => Response::HTTP_CONFLICT,
        };
    }
}
