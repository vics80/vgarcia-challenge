<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Product\Exception;

use App\VgarciaChallenge\Shared\Domain\Exception\DomainException;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;

use function sprintf;

final class ProductStockLimitExceededException extends DomainException
{
    public static function forSelector(ProductSelector $selector, int $requestedStock, int $maxStock): self
    {
        return new self(sprintf(
            'Product [%s] cannot have [%d] units because the maximum stock is [%d].',
            $selector->value,
            $requestedStock,
            $maxStock,
        ));
    }
}
