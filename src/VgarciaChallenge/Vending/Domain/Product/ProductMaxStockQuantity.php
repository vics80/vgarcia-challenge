<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Product;

use App\VgarciaChallenge\Shared\Domain\ValueObject\IntegerValueObject;

final class ProductMaxStockQuantity extends IntegerValueObject
{
    public const int MIN = 1;
}
