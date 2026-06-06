<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Money\Exception;

use DomainException;

use function sprintf;

final class ChangeNotAvailableException extends DomainException
{
    public static function forAmount(int $amountCents): self
    {
        return new self(sprintf('Change [%d] cannot be returned with the available coins.', $amountCents));
    }
}
