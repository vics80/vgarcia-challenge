<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus;

use App\VgarciaChallenge\Shared\Application\Query\QueryHandler;

use function sprintf;

final class TestQueryHandler implements QueryHandler
{
    public ?TestQuery $handledQuery = null;

    public function __invoke(TestQuery $query): string
    {
        $this->handledQuery = $query;

        return sprintf('result:%s', $query->criteria);
    }

    public function handles(): string
    {
        return TestQuery::class;
    }
}
