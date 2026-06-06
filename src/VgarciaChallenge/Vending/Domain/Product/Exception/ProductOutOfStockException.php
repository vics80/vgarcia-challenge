<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Product\Exception;

use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use DomainException;

use function sprintf;

final class ProductOutOfStockException extends DomainException
{
    public static function forSelector(ProductSelector $selector): self
    {
        return new self(sprintf('Product [%s] is out of stock.', $selector->value));
    }
}
