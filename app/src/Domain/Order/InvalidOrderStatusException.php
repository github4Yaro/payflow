<?php

declare(strict_types=1);

namespace App\Domain\Order;

use App\Domain\Shared\InvalidStatusException;

final class InvalidOrderStatusException extends InvalidStatusException
{
}
