<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Product;

use App\VgarciaChallenge\Shared\Domain\ValueObject\IntegerValueObject;

final class ProductStockQuantity extends IntegerValueObject
{
    public const int MIN = 0;

    public function decrement(int $quantity = 1): self
    {
        return new self($this->value() - $quantity);
    }
}
