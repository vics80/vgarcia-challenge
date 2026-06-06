<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception;

use DomainException;

final class CoinsNotFoundException extends DomainException
{
    public static function becauseNoCoinsWereInserted(): self
    {
        return new self('No inserted coins were found to return.');
    }
}
