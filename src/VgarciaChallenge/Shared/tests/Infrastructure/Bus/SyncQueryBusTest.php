<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Infrastructure\Bus;

use App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus\InvalidTestQueryHandler;
use App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus\TestQuery;
use App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus\TestQueryHandler;
use App\VgarciaChallenge\Shared\Application\Query\Exception\InvalidQueryHandlerException;
use App\VgarciaChallenge\Shared\Application\Query\Exception\QueryHandlerNotFoundException;
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
