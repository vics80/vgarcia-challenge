<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\Product\Product;

final readonly class SelectProductResult
{
    public function __construct(
        private Product $product,
        private Money $returnedChange,
    ) {
    }

    public function product(): Product
    {
        return $this->product;
    }

    public function returnedChange(): Money
    {
        return $this->returnedChange;
    }
}
