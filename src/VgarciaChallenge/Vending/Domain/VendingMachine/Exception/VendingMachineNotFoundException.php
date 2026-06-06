<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception;

use DomainException;

final class VendingMachineNotFoundException extends DomainException
{
    public static function becauseNoMachineWasConfigured(): self
    {
        return new self('No vending machine was found. Run setup before inserting coins.');
    }
}
