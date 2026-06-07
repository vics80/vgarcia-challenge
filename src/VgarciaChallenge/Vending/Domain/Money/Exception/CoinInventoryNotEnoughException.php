<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Money\Exception;

use App\VgarciaChallenge\Shared\Domain\Exception\DomainException;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;

use function sprintf;

final class CoinInventoryNotEnoughException extends DomainException
{
    public static function forCoin(Coin $coin, int $requestedQuantity, int $availableQuantity): self
    {
        return new self(sprintf(
            'Cannot remove [%d] coin(s) of [%s]. Available: [%d].',
            $requestedQuantity,
            $coin->decimalString(),
            $availableQuantity,
        ));
    }
}
