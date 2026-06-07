<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Infrastructure\Bus;

use App\VgarciaChallenge\Shared\Application\Command\Command;
use App\VgarciaChallenge\Shared\Application\Command\CommandBus;
use App\VgarciaChallenge\Shared\Application\Command\CommandHandler;
use App\VgarciaChallenge\Shared\Application\Command\Exception\CommandHandlerNotFoundException;
use App\VgarciaChallenge\Shared\Application\Command\Exception\InvalidCommandHandlerException;

use function is_callable;

final class SyncCommandBus implements CommandBus
{
    /** @var array<class-string<Command>, callable> */
    private array $handlers = [];

    /** @param iterable<CommandHandler> $handlers */
    public function __construct(iterable $handlers)
    {
        foreach ($handlers as $handler) {
            $this->registerHandler($handler);
        }
    }

    public function dispatch(Command $command): mixed
    {
        return ($this->handlerFor($command))($command);
    }

    private function registerHandler(CommandHandler $handler): void
    {
        if (!is_callable($handler)) {
            throw InvalidCommandHandlerException::forHandler($handler);
        }

        $this->handlers[$handler->handles()] = $handler;
    }

    private function handlerFor(Command $command): callable
    {
        $handler = $this->handlers[$command::class] ?? null;

        if (null === $handler) {
            throw CommandHandlerNotFoundException::forCommand($command);
        }

        return $handler;
    }
}
