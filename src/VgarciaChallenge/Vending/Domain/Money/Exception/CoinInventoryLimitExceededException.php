<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Money\Exception;

use App\VgarciaChallenge\Shared\Domain\Exception\DomainException;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;

use function sprintf;

final class CoinInventoryLimitExceededException extends DomainException
{
    public static function forCoin(Coin $coin, int $requestedQuantity, int $maxQuantity): self
    {
        return new self(sprintf(
            'Coin inventory for [%s] would be [%d], maximum is [%d].',
            $coin->decimalString(),
            $requestedQuantity,
            $maxQuantity,
        ));
    }
}
