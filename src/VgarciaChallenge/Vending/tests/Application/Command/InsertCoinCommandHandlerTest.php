<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Vending\Application\Command\InsertCoinCommand;
use App\VgarciaChallenge\Vending\Application\Command\InsertCoinCommandHandler;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\InvalidCoinException;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\VendingMachineNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;
use PHPUnit\Framework\TestCase;

final class InsertCoinCommandHandlerTest extends TestCase
{
    public function testInsertsCoinInFirstVendingMachine(): void
    {
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            Money::empty(),
            ProductInventory::empty(),
        );
        $repository = $this->createMock(VendingMachineRepository::class);
        $repository
            ->expects(self::once())
            ->method('findFirst')
            ->willReturn($vendingMachine);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (VendingMachine $savedVendingMachine): bool {
                return 25 === $savedVendingMachine->insertedMoney()->totalCents()
                    && 0 === $savedVendingMachine->availableChange()->totalCents();
            }));

        (new InsertCoinCommandHandler($repository))(new InsertCoinCommand('0.25'));
    }

    public function testHandlesInsertCoinCommand(): void
    {
        $repository = $this->createMock(VendingMachineRepository::class);

        self::assertSame(InsertCoinCommand::class, (new InsertCoinCommandHandler($repository))->handles());
    }

    public function testFailsWhenThereIsNoVendingMachine(): void
    {
        $repository = $this->createMock(VendingMachineRepository::class);
        $repository
            ->expects(self::once())
            ->method('findFirst')
            ->willReturn(null);
        $repository
            ->expects(self::never())
            ->method('save');

        $this->expectException(VendingMachineNotFoundException::class);

        (new InsertCoinCommandHandler($repository))(new InsertCoinCommand('0.25'));
    }

    public function testFailsWhenCoinIsNotAccepted(): void
    {
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            Money::empty(),
            ProductInventory::empty(),
        );
        $repository = $this->createMock(VendingMachineRepository::class);
        $repository
            ->expects(self::once())
            ->method('findFirst')
            ->willReturn($vendingMachine);
        $repository
            ->expects(self::never())
            ->method('save');

        $this->expectException(InvalidCoinException::class);

        (new InsertCoinCommandHandler($repository))(new InsertCoinCommand('0.50'));
    }
}
