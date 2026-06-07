<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus;

use App\VgarciaChallenge\Shared\Application\Query\QueryHandler;

final class InvalidTestQueryHandler implements QueryHandler
{
    public function handles(): string
    {
        return TestQuery::class;
    }
}
