<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Money\Exception;

use DomainException;

use function sprintf;

final class InvalidCoinException extends DomainException
{
    public static function fromCents(int $coinCents): self
    {
        return new self(sprintf('Coin [%d] is not accepted by this vending machine.', $coinCents));
    }

    public static function fromDecimalValue(string $coin): self
    {
        return new self(sprintf('Coin [%s] is not accepted by this vending machine.', $coin));
    }
}
