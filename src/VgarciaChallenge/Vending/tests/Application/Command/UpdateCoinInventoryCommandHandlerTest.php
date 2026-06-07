<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Vending\Application\Command\UpdateCoinInventoryCommand;
use App\VgarciaChallenge\Vending\Application\Command\UpdateCoinInventoryCommandHandler;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\CoinInventory;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\InvalidCoinException;
use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\VendingMachineNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;
use PHPUnit\Framework\TestCase;

final class UpdateCoinInventoryCommandHandlerTest extends TestCase
{
    public function testAddsCoinInventoryAndSavesVendingMachine(): void
    {
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            CoinInventory::fromCoinQuantities([Coin::TWENTY_FIVE_CENTS->cents() => 10]),
            ProductInventory::empty(),
        );
        $repository = $this->repository($vendingMachine);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (VendingMachine $savedVendingMachine): bool {
                return 15 === $savedVendingMachine->availableChange()->quantityOf(Coin::TWENTY_FIVE_CENTS);
            }));

        $result = (new UpdateCoinInventoryCommandHandler($repository))(new UpdateCoinInventoryCommand('0.25', 5));

        self::assertSame(Coin::TWENTY_FIVE_CENTS, $result->coin());
        self::assertSame(5, $result->quantity());
        self::assertSame(15, $result->inventoryQuantity()->value());
        self::assertSame(100, $result->maxInventoryQuantity()->value());
    }

    public function testRemovesCoinInventoryAndSavesVendingMachine(): void
    {
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            CoinInventory::fromCoinQuantitiesWithMax(
                [Coin::ONE_EURO->cents() => 10],
                [Coin::ONE_EURO->cents() => 25],
            ),
            ProductInventory::empty(),
        );
        $repository = $this->repository($vendingMachine);
        $repository
            ->expects(self::once())
            ->method('save');

        $result = (new UpdateCoinInventoryCommandHandler($repository))(new UpdateCoinInventoryCommand('1.00', -3));

        self::assertSame(Coin::ONE_EURO, $result->coin());
        self::assertSame(-3, $result->quantity());
        self::assertSame(7, $result->inventoryQuantity()->value());
        self::assertSame(25, $result->maxInventoryQuantity()->value());
    }

    public function testHandlesUpdateCoinInventoryCommand(): void
    {
        self::assertSame(
            UpdateCoinInventoryCommand::class,
            (new UpdateCoinInventoryCommandHandler($this->repository(null)))->handles(),
        );
    }

    public function testFailsWhenThereIsNoVendingMachine(): void
    {
        $repository = $this->repository(null);
        $repository
            ->expects(self::never())
            ->method('save');

        $this->expectException(VendingMachineNotFoundException::class);

        (new UpdateCoinInventoryCommandHandler($repository))(new UpdateCoinInventoryCommand('0.25', 5));
    }

    public function testFailsWhenCoinIsUnknown(): void
    {
        $repository = $this->repository(VendingMachine::create(
            VendingMachineId::random(),
            CoinInventory::empty(),
            ProductInventory::empty(),
        ));
        $repository
            ->expects(self::never())
            ->method('save');

        $this->expectException(InvalidCoinException::class);

        (new UpdateCoinInventoryCommandHandler($repository))(new UpdateCoinInventoryCommand('0.50', 5));
    }

    private function repository(?VendingMachine $vendingMachine): VendingMachineRepository
    {
        $repository = $this->createMock(VendingMachineRepository::class);
        $repository
            ->method('findFirst')
            ->willReturn($vendingMachine);

        return $repository;
    }
}
