<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console;

use App\Tests\VgarciaChallenge\Vending\Support\Infrastructure\UserInterface\Console\UpdateCoinInventoryTestCommandBus;
use App\VgarciaChallenge\Vending\Application\Command\UpdateCoinInventoryCommand;
use App\VgarciaChallenge\Vending\Application\Command\UpdateCoinInventoryResult;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\CoinInventoryQuantity;
use App\VgarciaChallenge\Vending\Domain\Money\CoinMaxInventoryQuantity;
use App\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console\AdminCoinInventoryConsoleCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;

final class AdminCoinInventoryConsoleCommandTest extends TestCase
{
    public function testDispatchesUpdateCoinInventoryCommandAndPrintsUpdatedInventory(): void
    {
        $commandBus = new UpdateCoinInventoryTestCommandBus(new UpdateCoinInventoryResult(
            Coin::TWENTY_FIVE_CENTS,
            5,
            new CoinInventoryQuantity(15),
            new CoinMaxInventoryQuantity(100),
        ));
        $commandTester = new CommandTester(new AdminCoinInventoryConsoleCommand($commandBus));

        $exitCode = $commandTester->execute(['coin' => '0.25', 'quantity' => '5']);

        self::assertSame(SymfonyCommand::SUCCESS, $exitCode);
        self::assertInstanceOf(UpdateCoinInventoryCommand::class, $commandBus->dispatchedCommand);
        self::assertSame('0.25', $commandBus->dispatchedCommand->coin());
        self::assertSame(5, $commandBus->dispatchedCommand->quantity());
        self::assertStringContainsString(
            'Updated coin inventory for 0.25 by +5. Current quantity: 15/100.',
            $commandTester->getDisplay(),
        );
    }

    public function testDispatchesNegativeQuantity(): void
    {
        $commandBus = new UpdateCoinInventoryTestCommandBus(new UpdateCoinInventoryResult(
            Coin::ONE_EURO,
            -3,
            new CoinInventoryQuantity(7),
            new CoinMaxInventoryQuantity(25),
        ));
        $commandTester = new CommandTester(new AdminCoinInventoryConsoleCommand($commandBus));

        $exitCode = $commandTester->execute(['coin' => '1.00', 'quantity' => '-3']);

        self::assertSame(SymfonyCommand::SUCCESS, $exitCode);
        self::assertInstanceOf(UpdateCoinInventoryCommand::class, $commandBus->dispatchedCommand);
        self::assertSame('1.00', $commandBus->dispatchedCommand->coin());
        self::assertSame(-3, $commandBus->dispatchedCommand->quantity());
        self::assertStringContainsString(
            'Updated coin inventory for 1.00 by -3. Current quantity: 7/25.',
            $commandTester->getDisplay(),
        );
    }

    public function testAcceptsNegativeQuantityFromArgvInput(): void
    {
        $argv = $_SERVER['argv'] ?? [];
        $commandBus = new UpdateCoinInventoryTestCommandBus(new UpdateCoinInventoryResult(
            Coin::ONE_EURO,
            -3,
            new CoinInventoryQuantity(7),
            new CoinMaxInventoryQuantity(25),
        ));
        $application = new Application();
        $application->setAutoExit(false);
        $application->add(new AdminCoinInventoryConsoleCommand($commandBus));

        try {
            $_SERVER['argv'] = ['bin/console', 'vending:admin:coins', '1.00', '-3', '--ansi'];

            $exitCode = $application->run(
                new ArgvInput($_SERVER['argv']),
                new BufferedOutput(),
            );
        } finally {
            $_SERVER['argv'] = $argv;
        }

        self::assertSame(SymfonyCommand::SUCCESS, $exitCode);
        self::assertInstanceOf(UpdateCoinInventoryCommand::class, $commandBus->dispatchedCommand);
        self::assertSame('1.00', $commandBus->dispatchedCommand->coin());
        self::assertSame(-3, $commandBus->dispatchedCommand->quantity());
    }

    public function testAcceptsNegativeQuantityFromAbbreviatedCommandArgvInput(): void
    {
        $argv = $_SERVER['argv'] ?? [];
        $commandBus = new UpdateCoinInventoryTestCommandBus(new UpdateCoinInventoryResult(
            Coin::TEN_CENTS,
            -2,
            new CoinInventoryQuantity(8),
            new CoinMaxInventoryQuantity(100),
        ));
        $application = new Application();
        $application->setAutoExit(false);
        $application->add(new AdminCoinInventoryConsoleCommand($commandBus));

        try {
            $_SERVER['argv'] = ['bin/console', 'ven:adm:coi', '0.1', '-2'];

            $exitCode = $application->run(
                new ArgvInput($_SERVER['argv']),
                new BufferedOutput(),
            );
        } finally {
            $_SERVER['argv'] = $argv;
        }

        self::assertSame(SymfonyCommand::SUCCESS, $exitCode);
        self::assertInstanceOf(UpdateCoinInventoryCommand::class, $commandBus->dispatchedCommand);
        self::assertSame('0.1', $commandBus->dispatchedCommand->coin());
        self::assertSame(-2, $commandBus->dispatchedCommand->quantity());
    }

    public function testFailsWhenQuantityIsMissing(): void
    {
        $commandBus = new UpdateCoinInventoryTestCommandBus(new UpdateCoinInventoryResult(
            Coin::TWENTY_FIVE_CENTS,
            5,
            new CoinInventoryQuantity(15),
            new CoinMaxInventoryQuantity(100),
        ));
        $commandTester = new CommandTester(new AdminCoinInventoryConsoleCommand($commandBus));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be an integer.');

        $commandTester->execute(['coin' => '0.25']);
    }

    public function testFailsWhenQuantityIsNotAnInteger(): void
    {
        $commandBus = new UpdateCoinInventoryTestCommandBus(new UpdateCoinInventoryResult(
            Coin::TWENTY_FIVE_CENTS,
            5,
            new CoinInventoryQuantity(15),
            new CoinMaxInventoryQuantity(100),
        ));
        $commandTester = new CommandTester(new AdminCoinInventoryConsoleCommand($commandBus));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be an integer.');

        $commandTester->execute(['coin' => '0.25', 'quantity' => 'abc']);
    }
}
