<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Invoice;

use App\Domain\Invoice\InvoiceSeries;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InvoiceSeriesTest extends TestCase
{
    #[DataProvider('generateYearsList')]
    public function testInvoiceSeries(int $year): void
    {
        $invoiceSeries = InvoiceSeries::forYear($year);
        $this->assertSame('INV-'.$year, (string) $invoiceSeries);
    }

    public function testYearBelowRangeIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InvoiceSeries::forYear(1999);
    }

    public function testYearAboveRangeIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InvoiceSeries::forYear(10000);
    }

    public function testInvoicesSeriesDates(): void
    {
        $date1 = new \DateTimeImmutable('2025-12-31 23:59:59');
        $date2 = new \DateTimeImmutable('2026-01-01 00:00:00');

        $this->assertSame('INV-2025', (string) InvoiceSeries::fromIssueDate($date1));
        $this->assertSame('INV-2026', (string) InvoiceSeries::fromIssueDate($date2));
    }

    /**
     * @return \Generator<string, array{int}>
     */
    public static function generateYearsList(): iterable
    {
        foreach ([2000, 2025, 5000, 9999] as $year) {
            yield "series by $year year" => [$year];
        }
    }
}
