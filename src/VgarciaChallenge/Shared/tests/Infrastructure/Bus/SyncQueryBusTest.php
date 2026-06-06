<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Infrastructure\Bus;

use App\VgarciaChallenge\Shared\Application\Query\Exception\InvalidQueryHandlerException;
use App\VgarciaChallenge\Shared\Application\Query\Exception\QueryHandlerNotFoundException;
use App\VgarciaChallenge\Shared\Application\Query\Query;
use App\VgarciaChallenge\Shared\Application\Query\QueryHandler;
use App\VgarciaChallenge\Shared\Infrastructure\Bus\SyncQueryBus;
use PHPUnit\Framework\TestCase;

final class SyncQueryBusTest extends TestCase
{
    public function testAsksQueryToItsHandler(): void
    {
        $handler = new TestQueryHandler();
        $queryBus = new SyncQueryBus([$handler]);
        $query = new TestQuery('products');

        $result = $queryBus->ask($query);

        self::assertSame('result:products', $result);
        self::assertSame($query, $handler->handledQuery);
    }

    public function testFailsWhenQueryHasNoHandler(): void
    {
        $queryBus = new SyncQueryBus([]);

        $this->expectException(QueryHandlerNotFoundException::class);

        $queryBus->ask(new TestQuery('products'));
    }

    public function testFailsWhenQueryHandlerIsNotCallable(): void
    {
        $this->expectException(InvalidQueryHandlerException::class);

        new SyncQueryBus([new InvalidTestQueryHandler()]);
    }
}

final class TestQuery implements Query
{
    public function __construct(
        public readonly string $criteria,
    ) {
    }
}

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

final class InvalidTestQueryHandler implements QueryHandler
{
    public function handles(): string
    {
        return TestQuery::class;
    }
}
