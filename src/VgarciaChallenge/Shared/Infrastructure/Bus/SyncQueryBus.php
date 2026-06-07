<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Infrastructure\Bus;

use App\VgarciaChallenge\Shared\Application\Query\Exception\InvalidQueryHandlerException;
use App\VgarciaChallenge\Shared\Application\Query\Exception\QueryHandlerNotFoundException;
use App\VgarciaChallenge\Shared\Application\Query\Query;
use App\VgarciaChallenge\Shared\Application\Query\QueryBus;
use App\VgarciaChallenge\Shared\Application\Query\QueryHandler;

use function is_callable;

final class SyncQueryBus implements QueryBus
{
    /** @var array<class-string<Query>, callable> */
    private array $handlers = [];

    /** @param iterable<QueryHandler> $handlers */
    public function __construct(iterable $handlers)
    {
        foreach ($handlers as $handler) {
            $this->registerHandler($handler);
        }
    }

    public function ask(Query $query): mixed
    {
        return ($this->handlerFor($query))($query);
    }

    private function registerHandler(QueryHandler $handler): void
    {
        if (!is_callable($handler)) {
            throw InvalidQueryHandlerException::forHandler($handler);
        }

        $this->handlers[$handler->handles()] = $handler;
    }

    private function handlerFor(Query $query): callable
    {
        $handler = $this->handlers[$query::class] ?? null;

        if (null === $handler) {
            throw QueryHandlerNotFoundException::forQuery($query);
        }

        return $handler;
    }
}
