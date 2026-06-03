<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Product\ProductStockQuantity;

final class ProductStockQuantityType extends IntegerValueObjectType
{
    public const string NAME = 'product_stock_quantity';

    protected function valueObjectClass(): string
    {
        return ProductStockQuantity::class;
    }
}
