<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Product;

use App\VgarciaChallenge\Shared\Domain\ValueObject\StringValueObject;

final class ProductName extends StringValueObject
{
    public const int MAX_LENGTH = 120;
}
