<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shared;

use App\Domain\Shared\BelowZeroException;
use App\Domain\Shared\Currency;
use App\Domain\Shared\Money;
use App\Domain\Shared\WrongCurrencyException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    #[DataProvider('generateSplit')]
    public function testSplit(int $total, int $parts): void
    {
        $money = new Money(Currency::USD, $total);
        $sum = array_sum(array_map(
            static fn (Money $m): int => $m->minorUnits,
            $money->split($parts)
        ));

        self::assertSame($total, $sum);
    }

    #[DataProvider('generateSplit')]
    public function testSplitCount(int $total, int $parts): void
    {
        $money = new Money(Currency::USD, $total);
        $count = count(array_map(
            static fn (Money $m): int => $m->minorUnits,
            $money->split($parts)
        ));

        self::assertSame($parts, $count);
    }

    #[DataProvider('generateSplit')]
    public function testSplitDiff(int $total, int $parts): void
    {
        $money = new Money(Currency::USD, $total);
        $units = array_map(static fn (Money $m): int => $m->minorUnits, $money->split($parts));
        self::assertLessThanOrEqual(1, max($units) - min($units));
    }

    #[DataProvider('generateSplit')]
    public function testSplitCurrency(int $total, int $parts): void
    {
        $money = new Money(Currency::USD, $total);
        $currency_list = array_unique(array_map(
            static fn (Money $m): string => $m->currency->value,
            $money->split($parts)
        ));
        self::assertSame(1, count($currency_list));
        self::assertSame(Currency::USD, Currency::from($currency_list[0]));
    }

    public function testWrongCurrency(): void
    {
        $money = new Money(Currency::USD, 10);
        $other_money = new Money(Currency::EUR, 10);
        $this->expectException(WrongCurrencyException::class);
        $money->add($other_money);
    }

    public function testBelowZero(): void
    {
        $this->expectException(BelowZeroException::class);
        new Money(Currency::USD, -10);
    }

    public function testInvalidSplit(): void
    {
        $money = new Money(Currency::USD, 10);
        $this->expectException(\InvalidArgumentException::class);
        $money->split(0);
    }

    /**
     * @return \Generator<string, array{int,int}>
     */
    public static function generateSplit(): iterable
    {
        foreach ([0, 1, 7, 100, 101, 999, 100_000] as $total) {
            foreach ([1, 2, 3, 4, 5, 7, 13] as $parts) {
                yield "$total into $parts" => [$total, $parts];
            }
        }
    }
}
