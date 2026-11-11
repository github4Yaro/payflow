<?php

declare(strict_types=1);

namespace App\Domain\Shared;

enum Currency: string
{
    case USD = 'usd';
    case EUR = 'eur';
    case CAD = 'cad';
}
