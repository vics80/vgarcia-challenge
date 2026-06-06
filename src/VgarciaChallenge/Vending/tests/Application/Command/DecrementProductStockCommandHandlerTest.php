<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Vending\Application\Command\DecrementProductStockCommand;
use App\VgarciaChallenge\Vending\Application\Command\DecrementProductStockCommandHandler;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\Product\Exception\ProductNotFoundException;
use App\VgarciaChallenge\Vending\Domain\Product\Product;
use App\VgarciaChallenge\Vending\Domain\Product\ProductId;
use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\Product\ProductStockQuantity;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\VendingMachineNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;
use PHPUnit\Framework\TestCase;

final class DecrementProductStockCommandHandlerTest extends TestCase
{
    public function testDecrementsProductStockAndSavesVendingMachine(): void
    {
        $product = $this->product(ProductSelector::WATER, 2);
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            Money::empty(),
            ProductInventory::fromProducts($product),
        );
        $repository = $this->repository($vendingMachine);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (VendingMachine $savedVendingMachine): bool {
                return 1 === $savedVendingMachine
                    ->productInventory()
                    ->find(ProductSelector::WATER)
                    ?->stockQuantity()
                    ->value();
            }));

        (new DecrementProductStockCommandHandler($repository))(new DecrementProductStockCommand('WATER'));
    }

    public function testHandlesDecrementProductStockCommand(): void
    {
        self::assertSame(
            DecrementProductStockCommand::class,
            (new DecrementProductStockCommandHandler($this->repository(null)))->handles(),
        );
    }

    public function testFailsWhenThereIsNoVendingMachine(): void
    {
        $repository = $this->repository(null);
        $repository
            ->expects(self::never())
            ->method('save');

        $this->expectException(VendingMachineNotFoundException::class);

        (new DecrementProductStockCommandHandler($repository))(new DecrementProductStockCommand('WATER'));
    }

    public function testFailsWhenSelectorIsUnknown(): void
    {
        $repository = $this->repository(VendingMachine::create(
            VendingMachineId::random(),
            Money::empty(),
            ProductInventory::empty(),
        ));
        $repository
            ->expects(self::never())
            ->method('save');

        $this->expectException(ProductNotFoundException::class);

        (new DecrementProductStockCommandHandler($repository))(new DecrementProductStockCommand('UNKNOWN'));
    }

    private function repository(?VendingMachine $vendingMachine): VendingMachineRepository
    {
        $repository = $this->createMock(VendingMachineRepository::class);
        $repository
            ->method('findFirst')
            ->willReturn($vendingMachine);

        return $repository;
    }

    private function product(ProductSelector $selector, int $stockQuantity): Product
    {
        return Product::create(
            ProductId::random(),
            $selector->defaultName(),
            $selector,
            $selector->defaultPrice(),
            new ProductStockQuantity($stockQuantity),
        );
    }
}
