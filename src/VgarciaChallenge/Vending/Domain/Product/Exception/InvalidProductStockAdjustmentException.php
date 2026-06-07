<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Product\Exception;

use App\VgarciaChallenge\Shared\Domain\Exception\DomainException;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;

use function sprintf;

final class InvalidProductStockAdjustmentException extends DomainException
{
    public static function forSelector(ProductSelector $selector): self
    {
        return new self(sprintf('Stock adjustment for product [%s] cannot be zero.', $selector->value));
    }
}
