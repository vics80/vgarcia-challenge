<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Query;

use App\VgarciaChallenge\Shared\Application\Query\Query;

final readonly class FindPurchasableProductQuery implements Query
{
    public function __construct(
        private string $selector,
    ) {
    }

    public function selector(): string
    {
        return $this->selector;
    }
}
