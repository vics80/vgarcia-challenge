<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Product\ProductMaxStockQuantity;

final class ProductMaxStockQuantityType extends IntegerValueObjectType
{
    public const string NAME = 'product_max_stock_quantity';

    protected function valueObjectClass(): string
    {
        return ProductMaxStockQuantity::class;
    }
}
