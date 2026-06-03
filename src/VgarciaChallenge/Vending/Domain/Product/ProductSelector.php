<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Product;

enum ProductSelector: string
{
    case WATER = 'WATER';
    case JUICE = 'JUICE';
    case SODA = 'SODA';

    public function defaultName(): ProductName
    {
        return new ProductName(match ($this) {
            self::WATER => 'Water',
            self::JUICE => 'Juice',
            self::SODA => 'Soda',
        });
    }

    public function defaultPrice(): ProductPrice
    {
        return ProductPrice::fromCents(match ($this) {
            self::WATER => 65,
            self::JUICE => 100,
            self::SODA => 150,
        });
    }
}
