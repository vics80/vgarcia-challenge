<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Support\Application\Query;

use App\VgarciaChallenge\Shared\Application\Query\Query;
use App\VgarciaChallenge\Shared\Application\Query\QueryBus;
use App\VgarciaChallenge\Vending\Domain\Product\Product;

final class SelectProductTestQueryBus implements QueryBus
{
    public ?Query $query = null;

    public function __construct(
        private readonly Product $product,
    ) {
    }

    public function ask(Query $query): mixed
    {
        $this->query = $query;

        return $this->product;
    }
}
