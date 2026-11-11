<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Invoice;

use App\Domain\Invoice\InvoiceStatus;
use App\Tests\Unit\Domain\Shared\AbstractStatusTestCase;

/**
 * @extends AbstractStatusTestCase<InvoiceStatus>
 */
final class InvoiceStatusTest extends AbstractStatusTestCase
{
    /** @return array<string, list<string>> */
    protected static function expected(): array
    {
        return [
            'issued' => ['paid', 'void', 'overdue'],
            'paid' => ['refunded'],
            'overdue' => ['void', 'paid'],
            'void' => [],
            'refunded' => [],
        ];
    }

    /** @return array<InvoiceStatus> */
    protected static function statusCases(): array
    {
        return InvoiceStatus::cases();
    }

    protected static function fromValue(int|string $v): InvoiceStatus
    {
        return InvoiceStatus::from($v);
    }

    protected static function terminalStatuses(): array
    {
        return ['void', 'refunded'];
    }

    /** @return array<string> */
    protected static function refundableList(): array
    {
        return ['paid'];
    }

    protected static function rootStatus(): InvoiceStatus
    {
        return InvoiceStatus::Issued;
    }

    public function testIsAllowToRefund(): void
    {
        $allStatuses = InvoiceStatusTest::statusCases();
        $refundableList = InvoiceStatusTest::refundableList();
        $refundStatus = InvoiceStatus::Refunded;
        $isAllowToRefund = [];
        foreach ($allStatuses as $status) {
            $isAllowToRefund[] = $status->canSetTo($refundStatus) ? $status->value : null;
        }
        $isAllowToRefund = array_filter($isAllowToRefund);

        $diff = array_diff($isAllowToRefund, $refundableList);
        $this->assertEmpty($diff, sprintf('Not refundable status(es): `%s` that allow to refund', implode(', ', $diff)));
    }

    public function testIfAllowToRefund(): void
    {
        $allStatuses = InvoiceStatusTest::statusCases();
        $refundableList = InvoiceStatusTest::refundableList();
        $refundStatus = InvoiceStatus::Refunded;
        $isAllowToRefund = [];
        foreach ($allStatuses as $status) {
            $isAllowToRefund[] = $status->canSetTo($refundStatus) ? $status->value : null;
        }
        $isAllowToRefund = array_filter($isAllowToRefund);

        $diff = array_diff($refundableList, $isAllowToRefund);
        $this->assertEmpty($diff, sprintf('Refundable status(es): `%s` that do(es) not allow to refund', implode(', ', $diff)));
    }
}
