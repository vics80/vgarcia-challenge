<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Infrastructure\Bus;

use App\VgarciaChallenge\Shared\Application\Command\Command;
use App\VgarciaChallenge\Shared\Application\Command\CommandHandler;
use App\VgarciaChallenge\Shared\Application\Command\Exception\CommandHandlerNotFoundException;
use App\VgarciaChallenge\Shared\Application\Command\Exception\InvalidCommandHandlerException;
use App\VgarciaChallenge\Shared\Infrastructure\Bus\SyncCommandBus;
use PHPUnit\Framework\TestCase;

final class SyncCommandBusTest extends TestCase
{
    public function testDispatchesCommandToItsHandler(): void
    {
        $handler = new TestCommandHandler();
        $commandBus = new SyncCommandBus([$handler]);

        $commandBus->dispatch(new TestCommand());

        self::assertTrue($handler->wasCalled);
    }

    public function testFailsWhenCommandHasNoHandler(): void
    {
        $commandBus = new SyncCommandBus([]);

        $this->expectException(CommandHandlerNotFoundException::class);

        $commandBus->dispatch(new TestCommand());
    }

    public function testFailsWhenCommandHandlerIsNotCallable(): void
    {
        $this->expectException(InvalidCommandHandlerException::class);

        new SyncCommandBus([new InvalidTestCommandHandler()]);
    }
}

final class TestCommand implements Command
{
}

final class TestCommandHandler implements CommandHandler
{
    public bool $wasCalled = false;

    public function __invoke(TestCommand $command): void
    {
        $this->wasCalled = true;
    }

    public function handles(): string
    {
        return TestCommand::class;
    }
}

final class InvalidTestCommandHandler implements CommandHandler
{
    public function handles(): string
    {
        return TestCommand::class;
    }
}
