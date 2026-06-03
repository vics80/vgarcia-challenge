<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Product\ProductPrice;

final class ProductPriceType extends IntegerValueObjectType
{
    public const string NAME = 'product_price';

    protected function valueObjectClass(): string
    {
        return ProductPrice::class;
    }
}
