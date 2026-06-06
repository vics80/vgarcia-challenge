<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console;

use App\VgarciaChallenge\Shared\Application\Command\Command;
use App\VgarciaChallenge\Shared\Application\Command\CommandBus;
use App\VgarciaChallenge\Vending\Application\Command\ReturnCoinsCommand;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console\ReturnCoinsConsoleCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Tester\CommandTester;

final class ReturnCoinsConsoleCommandTest extends TestCase
{
    public function testDispatchesReturnCoinsCommandAndPrintsReturnedCoins(): void
    {
        $commandBus = new ReturnCoinsTestCommandBus(Money::fromCoins(
            Coin::FIVE_CENTS,
            Coin::FIVE_CENTS,
            Coin::TWENTY_FIVE_CENTS,
        ));
        $commandTester = new CommandTester(new ReturnCoinsConsoleCommand($commandBus));

        $exitCode = $commandTester->execute([]);

        self::assertSame(SymfonyCommand::SUCCESS, $exitCode);
        self::assertInstanceOf(ReturnCoinsCommand::class, $commandBus->dispatchedCommand);
        self::assertStringContainsString('Returned coins: 0.05, 0.05, 0.25.', $commandTester->getDisplay());
    }
}

final class ReturnCoinsTestCommandBus implements CommandBus
{
    public ?Command $dispatchedCommand = null;

    public function __construct(
        private readonly Money $returnedMoney,
    ) {
    }

    public function dispatch(Command $command): mixed
    {
        $this->dispatchedCommand = $command;

        return $this->returnedMoney;
    }
}
