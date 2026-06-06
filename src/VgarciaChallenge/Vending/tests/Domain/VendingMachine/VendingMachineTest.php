<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\VendingMachine;

use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Event\CoinWasAdded;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class VendingMachineTest extends TestCase
{
    public function testCreatesVendingMachineAndExposesValues(): void
    {
        $id = VendingMachineId::random();
        $availableChange = Money::fromCoins(Coin::ONE_EURO);
        $productInventory = ProductInventory::empty();

        $vendingMachine = VendingMachine::create($id, $availableChange, $productInventory);

        self::assertSame($id, $vendingMachine->vendingMachineId());
        self::assertSame(0, $vendingMachine->insertedMoney()->totalCents());
        self::assertSame($availableChange, $vendingMachine->availableChange());
        self::assertSame($productInventory, $vendingMachine->productInventory());
        self::assertSame($vendingMachine->createdAt(), $vendingMachine->updatedAt());
    }

    public function testReconstitutesVendingMachineWithStateAndTimestamps(): void
    {
        $createdAt = new DateTimeImmutable('2026-01-01 10:00:00');
        $updatedAt = new DateTimeImmutable('2026-01-02 10:00:00');
        $productInventory = ProductInventory::empty();

        $vendingMachine = VendingMachine::reconstitute(
            VendingMachineId::random(),
            Money::fromCoins(Coin::FIVE_CENTS),
            Money::fromCoins(Coin::TEN_CENTS),
            $productInventory,
            $createdAt,
            $updatedAt,
        );

        self::assertSame(5, $vendingMachine->insertedMoney()->totalCents());
        self::assertSame(10, $vendingMachine->availableChange()->totalCents());
        self::assertSame($productInventory, $vendingMachine->productInventory());
        self::assertSame($createdAt, $vendingMachine->createdAt());
        self::assertSame($updatedAt, $vendingMachine->updatedAt());
    }

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
        self::assertSame($vendingMachine->vendingMachineId()->value(), $events[0]->aggregateId());
        self::assertSame([], $vendingMachine->pullDomainEvents());
    }

    public function testInsertCoinTouchesUpdatedAt(): void
    {
        $updatedAt = new DateTimeImmutable('2026-01-01 10:00:00');
        $vendingMachine = VendingMachine::reconstitute(
            VendingMachineId::random(),
            Money::empty(),
            Money::empty(),
            ProductInventory::empty(),
            new DateTimeImmutable('2026-01-01 09:00:00'),
            $updatedAt,
        );

        $vendingMachine->insertCoin(Coin::FIVE_CENTS);

        self::assertNotSame($updatedAt, $vendingMachine->updatedAt());
    }
}
