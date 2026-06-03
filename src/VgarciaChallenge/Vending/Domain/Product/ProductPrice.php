<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Product;

use App\VgarciaChallenge\Shared\Domain\ValueObject\IntegerValueObject;

final class ProductPrice extends IntegerValueObject
{
    public const int MIN = 1;

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public function cents(): int
    {
        return $this->value();
    }

    public function decimalString(): string
    {
        return sprintf('%d.%02d', intdiv($this->value(), 100), $this->value() % 100);
    }
}
