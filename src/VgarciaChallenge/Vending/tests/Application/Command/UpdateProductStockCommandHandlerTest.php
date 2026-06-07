<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Vending\Application\Command\UpdateProductStockCommand;
use App\VgarciaChallenge\Vending\Application\Command\UpdateProductStockCommandHandler;
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

final class UpdateProductStockCommandHandlerTest extends TestCase
{
    public function testAddsProductStockAndSavesVendingMachine(): void
    {
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            Money::empty(),
            ProductInventory::fromProducts($this->product(ProductSelector::WATER, 10)),
        );
        $repository = $this->repository($vendingMachine);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (VendingMachine $savedVendingMachine): bool {
                return 15 === $savedVendingMachine
                    ->productInventory()
                    ->find(ProductSelector::WATER)
                    ?->stockQuantity()
                    ->value();
            }));

        $result = (new UpdateProductStockCommandHandler($repository))(new UpdateProductStockCommand('WATER', 5));

        self::assertSame(ProductSelector::WATER, $result->selector());
        self::assertSame(5, $result->quantity());
        self::assertSame(15, $result->stockQuantity()->value());
        self::assertSame(20, $result->maxStockQuantity()->value());
    }

    public function testRemovesProductStockAndSavesVendingMachine(): void
    {
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            Money::empty(),
            ProductInventory::fromProducts($this->product(ProductSelector::SODA, 10)),
        );
        $repository = $this->repository($vendingMachine);
        $repository
            ->expects(self::once())
            ->method('save');

        $result = (new UpdateProductStockCommandHandler($repository))(new UpdateProductStockCommand('SODA', -3));

        self::assertSame(ProductSelector::SODA, $result->selector());
        self::assertSame(-3, $result->quantity());
        self::assertSame(7, $result->stockQuantity()->value());
        self::assertSame(10, $result->maxStockQuantity()->value());
    }

    public function testHandlesUpdateProductStockCommand(): void
    {
        self::assertSame(
            UpdateProductStockCommand::class,
            (new UpdateProductStockCommandHandler($this->repository(null)))->handles(),
        );
    }

    public function testFailsWhenThereIsNoVendingMachine(): void
    {
        $repository = $this->repository(null);
        $repository
            ->expects(self::never())
            ->method('save');

        $this->expectException(VendingMachineNotFoundException::class);

        (new UpdateProductStockCommandHandler($repository))(new UpdateProductStockCommand('WATER', 5));
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

        (new UpdateProductStockCommandHandler($repository))(new UpdateProductStockCommand('UNKNOWN', 5));
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
