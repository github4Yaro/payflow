<?php

namespace App\Domain\Enum;

enum Currency: string
{
    case USD = 'usd';
    case EUR = 'eur';
    case CAD = 'cad';
}
