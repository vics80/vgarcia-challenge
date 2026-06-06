<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Product\Exception;

use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use DomainException;

use function sprintf;

final class InsufficientMoneyException extends DomainException
{
    public static function forSelector(ProductSelector $selector, int $priceCents, int $insertedCents): self
    {
        return new self(sprintf(
            'Product [%s] costs [%d] cents, but only [%d] cents were inserted.',
            $selector->value,
            $priceCents,
            $insertedCents,
        ));
    }
}
