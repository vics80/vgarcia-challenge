<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Money\Exception;

use DomainException;

use function sprintf;

final class InvalidCoinQuantityException extends DomainException
{
    public static function fromQuantity(int $quantity): self
    {
        return new self(sprintf('Coin quantity [%d] cannot be negative.', $quantity));
    }
}
