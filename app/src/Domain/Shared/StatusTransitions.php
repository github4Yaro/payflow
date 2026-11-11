<?php

declare(strict_types=1);

namespace App\Domain\Shared;

interface StatusTransitions
{
    public function canSetTo(self $status): bool;
}
