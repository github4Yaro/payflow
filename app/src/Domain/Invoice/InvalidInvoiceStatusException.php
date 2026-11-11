<?php

declare(strict_types=1);

namespace App\Domain\Invoice;

use App\Domain\Shared\InvalidStatusException;

final class InvalidInvoiceStatusException extends InvalidStatusException
{
}
