<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Money;

use App\VgarciaChallenge\Vending\Domain\Money\Exception\InvalidCoinException;

use function trim;

enum Coin: int
{
    case FIVE_CENTS = 5;
    case TEN_CENTS = 10;
    case TWENTY_FIVE_CENTS = 25;
    case ONE_EURO = 100;

    public static function fromDecimalString(string $value): self
    {
        return match (trim($value)) {
            '0.05' => self::FIVE_CENTS,
            '0.10', '0.1' => self::TEN_CENTS,
            '0.25' => self::TWENTY_FIVE_CENTS,
            '1.00', '1.0', '1' => self::ONE_EURO,
            default => throw InvalidCoinException::fromDecimalValue($value),
        };
    }

    public function cents(): int
    {
        return $this->value;
    }

    public function decimalString(): string
    {
        return sprintf('%d.%02d', intdiv($this->value, 100), $this->value % 100);
    }

    public function defaultMaxInventoryQuantity(): CoinMaxInventoryQuantity
    {
        return new CoinMaxInventoryQuantity(100);
    }
}
