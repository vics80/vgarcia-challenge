<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Product\ProductName;

final class ProductNameType extends StringValueObjectType
{
    public const string NAME = 'product_name';

    protected function valueObjectClass(): string
    {
        return ProductName::class;
    }
}
