<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Money\Exception;

use App\VgarciaChallenge\Shared\Domain\Exception\DomainException;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;

use function sprintf;

final class InvalidCoinInventoryAdjustmentException extends DomainException
{
    public static function forCoin(Coin $coin): self
    {
        return new self(sprintf('Coin inventory adjustment for [%s] cannot be zero.', $coin->decimalString()));
    }
}
