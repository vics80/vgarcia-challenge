<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Product\Exception;

use App\VgarciaChallenge\Shared\Domain\Exception\DomainException;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;

use function sprintf;

final class ProductStockNotEnoughException extends DomainException
{
    public static function forSelector(ProductSelector $selector, int $requestedQuantity, int $currentStock): self
    {
        return new self(sprintf(
            'Product [%s] has [%d] units, cannot remove [%d].',
            $selector->value,
            $currentStock,
            $requestedQuantity,
        ));
    }
}
