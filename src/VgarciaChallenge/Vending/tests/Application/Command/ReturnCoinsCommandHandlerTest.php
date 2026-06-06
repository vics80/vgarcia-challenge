<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Vending\Application\Command\ReturnCoinsCommand;
use App\VgarciaChallenge\Vending\Application\Command\ReturnCoinsCommandHandler;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\CoinsNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\VendingMachineNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;
use PHPUnit\Framework\TestCase;

final class ReturnCoinsCommandHandlerTest extends TestCase
{
    public function testReturnsInsertedCoinsAndSavesVendingMachine(): void
    {
        $vendingMachine = VendingMachine::reconstitute(
            VendingMachineId::random(),
            Money::fromCoins(Coin::FIVE_CENTS, Coin::FIVE_CENTS, Coin::TWENTY_FIVE_CENTS),
            Money::fromCoins(Coin::FIVE_CENTS, Coin::FIVE_CENTS, Coin::TWENTY_FIVE_CENTS),
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
                return 0 === $savedVendingMachine->insertedMoney()->totalCents()
                    && 0 === $savedVendingMachine->availableChange()->totalCents();
            }));

        $returnedMoney = (new ReturnCoinsCommandHandler($repository))(new ReturnCoinsCommand());

        self::assertSame(35, $returnedMoney->totalCents());
        self::assertSame(2, $returnedMoney->quantityOf(Coin::FIVE_CENTS));
        self::assertSame(1, $returnedMoney->quantityOf(Coin::TWENTY_FIVE_CENTS));
    }

    public function testHandlesReturnCoinsCommand(): void
    {
        $repository = $this->createMock(VendingMachineRepository::class);

        self::assertSame(ReturnCoinsCommand::class, (new ReturnCoinsCommandHandler($repository))->handles());
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

        (new ReturnCoinsCommandHandler($repository))(new ReturnCoinsCommand());
    }

    public function testFailsWhenThereAreNoInsertedCoinsToReturn(): void
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

        $this->expectException(CoinsNotFoundException::class);

        (new ReturnCoinsCommandHandler($repository))(new ReturnCoinsCommand());
    }
}
