<?php

namespace App\Domain\VO;

use App\Domain\Enum\Currency;
use App\Domain\Exception\BelowZeroException;
use App\Domain\Exception\WrongCurrencyException;

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

    public function add(Money $new): Money
    {
        if ($new->currency !== $this->currency) {
            throw new WrongCurrencyException('try to add wrong currency');
        }

        return new self(currency: $this->currency, minorUnits: $this->minorUnits + $new->minorUnits);
    }

    /**
     * @return Money[]
     */
    public function split(int $num): array
    {
        if ($num < 1) {
            throw new BelowZeroException('can\'t split to 0 or below');
        }
        $remainder = $this->minorUnits % $num;
        $intdiv = intdiv($this->minorUnits, $num);
        $divs = [];
        for ($i = 1; $i <= $num; ++$i) {
            if (1 === $i && $remainder > 0) {
                ++$intdiv;
            }
            $divs[] = new self(currency: $this->currency, minorUnits: $intdiv);
        }

        return $divs;
    }
}
