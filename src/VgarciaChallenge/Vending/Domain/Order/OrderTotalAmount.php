<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Order;

use App\VgarciaChallenge\Shared\Domain\ValueObject\IntegerValueObject;

final class OrderTotalAmount extends IntegerValueObject
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
}
