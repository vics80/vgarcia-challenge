<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Infrastructure\Bus;

use App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus\InvalidTestCommandHandler;
use App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus\TestCommand;
use App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus\TestCommandHandler;
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

        $result = $commandBus->dispatch(new TestCommand());

        self::assertTrue($handler->wasCalled);
        self::assertSame('handled', $result);
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
