<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Product\Exception;

use DomainException;

use function sprintf;

final class ProductNotFoundException extends DomainException
{
    public static function forSelector(string $selector): self
    {
        return new self(sprintf('Product [%s] was not found.', $selector));
    }
}
