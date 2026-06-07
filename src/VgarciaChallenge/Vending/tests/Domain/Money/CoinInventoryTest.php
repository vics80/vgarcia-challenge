<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\Money;

use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\CoinInventory;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\CoinInventoryLimitExceededException;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\CoinInventoryNotEnoughException;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\InvalidCoinException;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\InvalidCoinInventoryAdjustmentException;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\InvalidCoinQuantityException;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use PHPUnit\Framework\TestCase;

final class CoinInventoryTest extends TestCase
{
    public function testCreatesEmptyInventoryWithDefaultMaxQuantities(): void
    {
        $inventory = CoinInventory::empty();

        self::assertTrue($inventory->isEmpty());
        self::assertSame(0, $inventory->totalCents());
        self::assertSame(0, $inventory->quantityOf(Coin::FIVE_CENTS));
        self::assertSame(100, $inventory->maxQuantityOf(Coin::FIVE_CENTS)->value());
        self::assertSame([], $inventory->toPrimitives());
    }

    public function testCreatesFromCoinQuantitiesAndNormalizesPrimitives(): void
    {
        $inventory = CoinInventory::fromCoinQuantities([
            '100' => 1,
            '25' => 2,
        ]);

        self::assertSame(150, $inventory->totalCents());
        self::assertSame([
            ['coinCents' => 25, 'quantity' => 2, 'maxQuantity' => 100],
            ['coinCents' => 100, 'quantity' => 1, 'maxQuantity' => 100],
        ], $inventory->toPrimitives());
    }

    public function testCreatesFromPrimitivesWithConfiguredMaxQuantities(): void
    {
        $inventory = CoinInventory::fromPrimitives([
            ['coinCents' => Coin::TEN_CENTS->cents(), 'quantity' => 2, 'maxQuantity' => 3],
            ['coinCents' => Coin::FIVE_CENTS->cents(), 'quantity' => 0, 'maxQuantity' => 5],
        ]);

        self::assertSame(20, $inventory->totalCents());
        self::assertSame(3, $inventory->maxQuantityOf(Coin::TEN_CENTS)->value());
        self::assertSame(5, $inventory->maxQuantityOf(Coin::FIVE_CENTS)->value());
        self::assertSame([
            ['coinCents' => 5, 'quantity' => 0, 'maxQuantity' => 5],
            ['coinCents' => 10, 'quantity' => 2, 'maxQuantity' => 3],
        ], $inventory->toPrimitives());
    }

    public function testCreatesFromMoney(): void
    {
        $inventory = CoinInventory::fromMoney(Money::fromCoins(Coin::FIVE_CENTS, Coin::FIVE_CENTS));

        self::assertSame(10, $inventory->totalCents());
        self::assertSame(2, $inventory->quantityOf(Coin::FIVE_CENTS));
    }

    public function testChangesCoinQuantityImmutably(): void
    {
        $inventory = CoinInventory::fromCoinQuantities([Coin::FIVE_CENTS->cents() => 2]);

        $updatedInventory = $inventory
            ->changeCoinQuantity(Coin::FIVE_CENTS, 3)
            ->changeCoinQuantity(Coin::FIVE_CENTS, -1);

        self::assertSame(2, $inventory->quantityOf(Coin::FIVE_CENTS));
        self::assertSame(4, $updatedInventory->quantityOf(Coin::FIVE_CENTS));
    }

    public function testSubtractsReturnedMoney(): void
    {
        $inventory = CoinInventory::fromCoinQuantities([
            Coin::FIVE_CENTS->cents() => 2,
            Coin::TWENTY_FIVE_CENTS->cents() => 1,
        ]);

        $updatedInventory = $inventory->subtract(Money::fromCoins(Coin::FIVE_CENTS, Coin::TWENTY_FIVE_CENTS));

        self::assertSame(1, $updatedInventory->quantityOf(Coin::FIVE_CENTS));
        self::assertSame(0, $updatedInventory->quantityOf(Coin::TWENTY_FIVE_CENTS));
    }

    public function testFailsWhenCoinIsUnsupported(): void
    {
        $this->expectException(InvalidCoinException::class);

        CoinInventory::fromCoinQuantities([1 => 1]);
    }

    public function testFailsWhenQuantityIsNegative(): void
    {
        $this->expectException(InvalidCoinQuantityException::class);

        CoinInventory::fromCoinQuantities([Coin::FIVE_CENTS->cents() => -1]);
    }

    public function testFailsWhenAdjustmentIsZero(): void
    {
        $this->expectException(InvalidCoinInventoryAdjustmentException::class);

        CoinInventory::empty()->changeCoinQuantity(Coin::FIVE_CENTS, 0);
    }

    public function testFailsWhenRemovingMoreCoinsThanAvailable(): void
    {
        $this->expectException(CoinInventoryNotEnoughException::class);

        CoinInventory::fromCoinQuantities([Coin::FIVE_CENTS->cents() => 2])
            ->changeCoinQuantity(Coin::FIVE_CENTS, -3);
    }

    public function testFailsWhenAddingMoreCoinsThanMaxQuantity(): void
    {
        $this->expectException(CoinInventoryLimitExceededException::class);

        CoinInventory::fromCoinQuantitiesWithMax(
            [Coin::FIVE_CENTS->cents() => 2],
            [Coin::FIVE_CENTS->cents() => 3],
        )->changeCoinQuantity(Coin::FIVE_CENTS, 2);
    }
}
