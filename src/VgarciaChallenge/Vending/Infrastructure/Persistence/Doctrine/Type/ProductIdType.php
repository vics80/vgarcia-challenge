<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Product\ProductId;

final class ProductIdType extends UuidValueObjectType
{
    public const string NAME = 'product_id';

    protected function valueObjectClass(): string
    {
        return ProductId::class;
    }
}
