<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\VendingMachine;

use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Event\CoinWasAdded;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use PHPUnit\Framework\TestCase;

final class VendingMachineTest extends TestCase
{
    public function testInsertCoinAddsCoinToInsertedMoneyOnly(): void
    {
        $availableChange = Money::fromCoinQuantities([
            Coin::FIVE_CENTS->cents() => 10,
            Coin::TEN_CENTS->cents() => 10,
        ]);
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            $availableChange,
            ProductInventory::empty(),
        );

        $vendingMachine->insertCoin(Coin::TWENTY_FIVE_CENTS);

        self::assertSame(25, $vendingMachine->insertedMoney()->totalCents());
        self::assertSame(150, $vendingMachine->availableChange()->totalCents());
    }

    public function testInsertCoinRecordsDomainEvent(): void
    {
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            Money::empty(),
            ProductInventory::empty(),
        );

        $vendingMachine->insertCoin(Coin::ONE_EURO);

        $events = $vendingMachine->pullDomainEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(CoinWasAdded::class, $events[0]);
        self::assertSame(100, $events[0]->coinCents());
        self::assertSame(100, $events[0]->insertedMoneyTotalCents());
        self::assertSame([], $vendingMachine->pullDomainEvents());
    }
}
