<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Vending\Domain\Product\ProductMaxStockQuantity;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\Product\ProductStockQuantity;

final readonly class UpdateProductStockResult
{
    public function __construct(
        private ProductSelector $selector,
        private int $quantity,
        private ProductStockQuantity $stockQuantity,
        private ProductMaxStockQuantity $maxStockQuantity,
    ) {
    }

    public function selector(): ProductSelector
    {
        return $this->selector;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function stockQuantity(): ProductStockQuantity
    {
        return $this->stockQuantity;
    }

    public function maxStockQuantity(): ProductMaxStockQuantity
    {
        return $this->maxStockQuantity;
    }
}
