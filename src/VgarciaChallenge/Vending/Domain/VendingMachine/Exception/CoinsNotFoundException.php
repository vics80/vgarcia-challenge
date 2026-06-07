<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception;

use App\VgarciaChallenge\Shared\Domain\Exception\DomainException;

final class CoinsNotFoundException extends DomainException
{
    public static function becauseNoCoinsWereInserted(): self
    {
        return new self('No inserted coins were found to return.');
    }
}
