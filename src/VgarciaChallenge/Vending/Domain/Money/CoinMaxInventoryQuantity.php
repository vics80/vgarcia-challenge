<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Money;

use App\VgarciaChallenge\Shared\Domain\ValueObject\IntegerValueObject;

final class CoinMaxInventoryQuantity extends IntegerValueObject
{
    public const int MIN = 1;
}
