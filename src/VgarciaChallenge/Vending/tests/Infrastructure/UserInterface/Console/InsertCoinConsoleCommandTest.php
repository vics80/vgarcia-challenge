<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console;

use App\VgarciaChallenge\Shared\Application\Command\Command;
use App\VgarciaChallenge\Shared\Application\Command\CommandBus;
use App\VgarciaChallenge\Vending\Application\Command\InsertCoinCommand;
use App\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console\InsertCoinConsoleCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Tester\CommandTester;

final class InsertCoinConsoleCommandTest extends TestCase
{
    public function testDispatchesInsertCoinCommand(): void
    {
        $commandBus = new TestCommandBus();
        $commandTester = new CommandTester(new InsertCoinConsoleCommand($commandBus));

        $exitCode = $commandTester->execute(['coin' => '0.25']);

        self::assertSame(SymfonyCommand::SUCCESS, $exitCode);
        self::assertInstanceOf(InsertCoinCommand::class, $commandBus->dispatchedCommand);
        self::assertSame('0.25', $commandBus->dispatchedCommand->coin());
    }
}

final class TestCommandBus implements CommandBus
{
    public ?Command $dispatchedCommand = null;

    public function dispatch(Command $command): mixed
    {
        $this->dispatchedCommand = $command;

        return null;
    }
}
