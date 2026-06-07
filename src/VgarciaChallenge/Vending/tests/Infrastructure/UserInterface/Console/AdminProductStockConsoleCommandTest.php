<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console;

use App\Tests\VgarciaChallenge\Vending\Support\Infrastructure\UserInterface\Console\UpdateProductStockTestCommandBus;
use App\VgarciaChallenge\Vending\Application\Command\UpdateProductStockCommand;
use App\VgarciaChallenge\Vending\Application\Command\UpdateProductStockResult;
use App\VgarciaChallenge\Vending\Domain\Product\ProductMaxStockQuantity;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\Product\ProductStockQuantity;
use App\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console\AdminProductStockConsoleCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;

final class AdminProductStockConsoleCommandTest extends TestCase
{
    public function testDispatchesUpdateProductStockCommandAndPrintsUpdatedStock(): void
    {
        $commandBus = new UpdateProductStockTestCommandBus(new UpdateProductStockResult(
            ProductSelector::WATER,
            5,
            new ProductStockQuantity(15),
            new ProductMaxStockQuantity(20),
        ));
        $commandTester = new CommandTester(new AdminProductStockConsoleCommand($commandBus));

        $exitCode = $commandTester->execute(['selector' => 'WATER', 'quantity' => '5']);

        self::assertSame(SymfonyCommand::SUCCESS, $exitCode);
        self::assertInstanceOf(UpdateProductStockCommand::class, $commandBus->dispatchedCommand);
        self::assertSame('WATER', $commandBus->dispatchedCommand->selector());
        self::assertSame(5, $commandBus->dispatchedCommand->quantity());
        self::assertStringContainsString(
            'Updated stock for WATER by +5. Current stock: 15/20.',
            $commandTester->getDisplay(),
        );
    }

    public function testDispatchesNegativeQuantity(): void
    {
        $commandBus = new UpdateProductStockTestCommandBus(new UpdateProductStockResult(
            ProductSelector::SODA,
            -3,
            new ProductStockQuantity(7),
            new ProductMaxStockQuantity(10),
        ));
        $commandTester = new CommandTester(new AdminProductStockConsoleCommand($commandBus));

        $exitCode = $commandTester->execute(['selector' => 'SODA', 'quantity' => '-3']);

        self::assertSame(SymfonyCommand::SUCCESS, $exitCode);
        self::assertInstanceOf(UpdateProductStockCommand::class, $commandBus->dispatchedCommand);
        self::assertSame('SODA', $commandBus->dispatchedCommand->selector());
        self::assertSame(-3, $commandBus->dispatchedCommand->quantity());
        self::assertStringContainsString(
            'Updated stock for SODA by -3. Current stock: 7/10.',
            $commandTester->getDisplay(),
        );
    }

    public function testAcceptsNegativeQuantityFromArgvInput(): void
    {
        $argv = $_SERVER['argv'] ?? [];
        $commandBus = new UpdateProductStockTestCommandBus(new UpdateProductStockResult(
            ProductSelector::SODA,
            -3,
            new ProductStockQuantity(7),
            new ProductMaxStockQuantity(10),
        ));
        $application = new Application();
        $application->setAutoExit(false);
        $application->add(new AdminProductStockConsoleCommand($commandBus));

        try {
            $_SERVER['argv'] = ['bin/console', 'vending:admin:stock', 'SODA', '-3', '--ansi'];

            $exitCode = $application->run(
                new ArgvInput($_SERVER['argv']),
                new BufferedOutput(),
            );
        } finally {
            $_SERVER['argv'] = $argv;
        }

        self::assertSame(SymfonyCommand::SUCCESS, $exitCode);
        self::assertInstanceOf(UpdateProductStockCommand::class, $commandBus->dispatchedCommand);
        self::assertSame('SODA', $commandBus->dispatchedCommand->selector());
        self::assertSame(-3, $commandBus->dispatchedCommand->quantity());
    }

    public function testAcceptsNegativeQuantityFromAbbreviatedCommandArgvInput(): void
    {
        $argv = $_SERVER['argv'] ?? [];
        $commandBus = new UpdateProductStockTestCommandBus(new UpdateProductStockResult(
            ProductSelector::WATER,
            -5,
            new ProductStockQuantity(5),
            new ProductMaxStockQuantity(20),
        ));
        $application = new Application();
        $application->setAutoExit(false);
        $application->add(new AdminProductStockConsoleCommand($commandBus));

        try {
            $_SERVER['argv'] = ['bin/console', 'ven:adm:sto', 'WATER', '-5'];

            $exitCode = $application->run(
                new ArgvInput($_SERVER['argv']),
                new BufferedOutput(),
            );
        } finally {
            $_SERVER['argv'] = $argv;
        }

        self::assertSame(SymfonyCommand::SUCCESS, $exitCode);
        self::assertInstanceOf(UpdateProductStockCommand::class, $commandBus->dispatchedCommand);
        self::assertSame('WATER', $commandBus->dispatchedCommand->selector());
        self::assertSame(-5, $commandBus->dispatchedCommand->quantity());
    }

    public function testFailsWhenQuantityIsMissing(): void
    {
        $commandBus = new UpdateProductStockTestCommandBus(new UpdateProductStockResult(
            ProductSelector::WATER,
            5,
            new ProductStockQuantity(15),
            new ProductMaxStockQuantity(20),
        ));
        $commandTester = new CommandTester(new AdminProductStockConsoleCommand($commandBus));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be an integer.');

        $commandTester->execute(['selector' => 'WATER']);
    }


    public function testFailsWhenQuantityIsNotAnInteger(): void
    {
        $commandBus = new UpdateProductStockTestCommandBus(new UpdateProductStockResult(
            ProductSelector::WATER,
            5,
            new ProductStockQuantity(15),
            new ProductMaxStockQuantity(20),
        ));
        $commandTester = new CommandTester(new AdminProductStockConsoleCommand($commandBus));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be an integer.');

        $commandTester->execute(['selector' => 'WATER', 'quantity' => 'abc']);
    }
}
