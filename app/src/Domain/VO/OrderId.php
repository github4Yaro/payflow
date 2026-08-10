<?php

namespace App\Domain\VO;

use Symfony\Component\Uid\Uuid;

final readonly class OrderId
{
    public Uuid $id;

    public function __construct()
    {
        $this->id = Uuid::v7();
    }
}
