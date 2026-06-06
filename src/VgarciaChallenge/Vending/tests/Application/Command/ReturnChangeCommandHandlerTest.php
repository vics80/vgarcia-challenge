<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Vending\Application\Command\ReturnChangeCommand;
use App\VgarciaChallenge\Vending\Application\Command\ReturnChangeCommandHandler;
use App\VgarciaChallenge\Vending\Domain\Money\ChangeCalculator;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\VendingMachineNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;
use PHPUnit\Framework\TestCase;

final class ReturnChangeCommandHandlerTest extends TestCase
{
    public function testReturnsChangeWithFewestCoinsClearsInsertedMoneyAndSavesVendingMachine(): void
    {
        $vendingMachine = VendingMachine::reconstitute(
            VendingMachineId::random(),
            Money::fromCoins(Coin::ONE_EURO),
            Money::fromCoins(Coin::ONE_EURO, Coin::TWENTY_FIVE_CENTS, Coin::TEN_CENTS),
            ProductInventory::empty(),
        );
        $repository = $this->repository($vendingMachine);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (VendingMachine $savedVendingMachine): bool {
                return 0 === $savedVendingMachine->insertedMoney()->totalCents()
                    && 100 === $savedVendingMachine->availableChange()->totalCents();
            }));

        $returnedChange = (new ReturnChangeCommandHandler($repository, new ChangeCalculator()))(
            new ReturnChangeCommand(65),
        );

        self::assertSame(35, $returnedChange->totalCents());
        self::assertSame(1, $returnedChange->quantityOf(Coin::TWENTY_FIVE_CENTS));
        self::assertSame(1, $returnedChange->quantityOf(Coin::TEN_CENTS));
    }

    public function testHandlesReturnChangeCommand(): void
    {
        self::assertSame(
            ReturnChangeCommand::class,
            (new ReturnChangeCommandHandler($this->repository(null), new ChangeCalculator()))->handles(),
        );
    }

    public function testFailsWhenThereIsNoVendingMachine(): void
    {
        $repository = $this->repository(null);
        $repository
            ->expects(self::never())
            ->method('save');

        $this->expectException(VendingMachineNotFoundException::class);

        (new ReturnChangeCommandHandler($repository, new ChangeCalculator()))(new ReturnChangeCommand(65));
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
