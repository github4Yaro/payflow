<?php

declare(strict_types=1);

namespace App\Domain\Shared;

final readonly class Money
{
    public function __construct(
        public Currency $currency,
        public int $minorUnits,
    ) {
        if ($this->minorUnits < 0) {
            throw new BelowZeroException('amount can\'t be below zero');
        }
    }

    public function add(Money $other): Money
    {
        if ($other->currency !== $this->currency) {
            throw new WrongCurrencyException('try to add wrong currency');
        }

        return new self(currency: $this->currency, minorUnits: $this->minorUnits + $other->minorUnits);
    }

    public function equals(Money $other): bool
    {
        return $other->currency === $this->currency && $other->minorUnits === $this->minorUnits;
    }

    /**
     * @return non-empty-list<Money>
     */
    public function split(int $num): array
    {
        if ($num < 1) {
            throw new \InvalidArgumentException('can\'t split to 0 or below');
        }
        $remainder = $this->minorUnits % $num;
        $intdiv = intdiv($this->minorUnits, $num);
        $divs = [];
        for ($i = 1; $i <= $num; ++$i) {
            $units = $intdiv;
            if ($remainder > 0) {
                ++$units;
                --$remainder;
            }
            $divs[] = new self(currency: $this->currency, minorUnits: $units);
        }

        return $divs;
    }
}
