<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console;

use App\Tests\VgarciaChallenge\Vending\Support\Infrastructure\UserInterface\Console\RecordingCommandBus;
use App\VgarciaChallenge\Vending\Application\Command\InsertCoinCommand;
use App\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console\InsertCoinConsoleCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Tester\CommandTester;

final class InsertCoinConsoleCommandTest extends TestCase
{
    public function testDispatchesInsertCoinCommand(): void
    {
        $commandBus = new RecordingCommandBus();
        $commandTester = new CommandTester(new InsertCoinConsoleCommand($commandBus));

        $exitCode = $commandTester->execute(['coin' => '0.25']);

        self::assertSame(SymfonyCommand::SUCCESS, $exitCode);
        self::assertInstanceOf(InsertCoinCommand::class, $commandBus->dispatchedCommand);
        self::assertSame('0.25', $commandBus->dispatchedCommand->coin());
    }
}
