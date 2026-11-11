<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order;

use App\Domain\Order\OrderStatus;
use App\Tests\Unit\Domain\Shared\AbstractStatusTestCase;

/**
 * @extends AbstractStatusTestCase<OrderStatus>
 */
final class OrderStatusTest extends AbstractStatusTestCase
{
    /** @return array<int|string, list<int|string>> */
    protected static function expected(): array
    {
        return [
            'draft' => ['new'],
            'new' => ['processing', 'canceled'],
            'disputed' => ['completed', 'canceled'],
            'processing' => ['completed', 'disputed', 'canceled'],
            'completed' => [],
            'canceled' => [],
        ];
    }

    /** @return array<string> */
    protected static function terminalStatuses(): array
    {
        return ['completed', 'canceled'];
    }

    /** @return array<OrderStatus> */
    protected static function statusCases(): array
    {
        return OrderStatus::cases();
    }

    protected static function fromValue(int|string $v): OrderStatus
    {
        return OrderStatus::from($v);
    }

    protected static function rootStatus(): OrderStatus
    {
        return OrderStatus::Draft;
    }
}
