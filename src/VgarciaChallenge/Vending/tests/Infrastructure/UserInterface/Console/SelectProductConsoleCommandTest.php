<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console;

use App\Tests\VgarciaChallenge\Vending\Support\Infrastructure\UserInterface\Console\SelectProductTestCommandBus;
use App\VgarciaChallenge\Vending\Application\Command\SelectProductCommand;
use App\VgarciaChallenge\Vending\Application\Command\SelectProductResult;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\Product\Product;
use App\VgarciaChallenge\Vending\Domain\Product\ProductId;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\Product\ProductStockQuantity;
use App\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console\SelectProductConsoleCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Tester\CommandTester;

final class SelectProductConsoleCommandTest extends TestCase
{
    public function testDispatchesSelectProductCommandAndPrintsDispensedProductWithoutChange(): void
    {
        $commandBus = new SelectProductTestCommandBus(new SelectProductResult(
            $this->product(ProductSelector::WATER),
            Money::empty(),
        ));
        $commandTester = new CommandTester(new SelectProductConsoleCommand($commandBus));

        $exitCode = $commandTester->execute(['selector' => 'WATER']);

        self::assertSame(SymfonyCommand::SUCCESS, $exitCode);
        self::assertInstanceOf(SelectProductCommand::class, $commandBus->dispatchedCommand);
        self::assertSame('WATER', $commandBus->dispatchedCommand->selector());
        self::assertStringContainsString('Dispensed product: Water (WATER).', $commandTester->getDisplay());
        self::assertStringContainsString('No change returned.', $commandTester->getDisplay());
    }

    public function testPrintsReturnedChange(): void
    {
        $commandBus = new SelectProductTestCommandBus(new SelectProductResult(
            $this->product(ProductSelector::WATER),
            Money::fromCoins(Coin::TWENTY_FIVE_CENTS, Coin::TEN_CENTS),
        ));
        $commandTester = new CommandTester(new SelectProductConsoleCommand($commandBus));

        $exitCode = $commandTester->execute(['selector' => 'WATER']);

        self::assertSame(SymfonyCommand::SUCCESS, $exitCode);
        self::assertStringContainsString(
            'Returned change: 0.10, 0.25 (0.35 total).',
            $commandTester->getDisplay(),
        );
    }

    private function product(ProductSelector $selector): Product
    {
        return Product::create(
            ProductId::random(),
            $selector->defaultName(),
            $selector,
            $selector->defaultPrice(),
            new ProductStockQuantity(10),
        );
    }
}
