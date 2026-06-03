<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Money;

enum Coin: int
{
    case FIVE_CENTS = 5;
    case TEN_CENTS = 10;
    case TWENTY_FIVE_CENTS = 25;
    case ONE_EURO = 100;

    public function cents(): int
    {
        return $this->value;
    }

    public function decimalString(): string
    {
        return sprintf('%d.%02d', intdiv($this->value, 100), $this->value % 100);
    }
}
