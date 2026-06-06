<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\Money;

use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\InvalidCoinException;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\InvalidCoinQuantityException;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testCreatesEmptyMoney(): void
    {
        $money = Money::empty();

        self::assertTrue($money->isEmpty());
        self::assertSame(0, $money->totalCents());
        self::assertSame('0.00', $money->decimalString());
        self::assertSame(0, $money->quantityOf(Coin::FIVE_CENTS));
        self::assertSame([], $money->toPrimitives());
    }

    public function testCalculatesTotalCentsFromCoins(): void
    {
        $money = Money::fromCoins(
            Coin::FIVE_CENTS,
            Coin::TEN_CENTS,
            Coin::TWENTY_FIVE_CENTS,
            Coin::ONE_EURO,
            Coin::ONE_EURO,
        );

        self::assertSame(240, $money->totalCents());
        self::assertSame('2.40', $money->decimalString());
        self::assertSame(2, $money->quantityOf(Coin::ONE_EURO));
    }

    public function testCreatesFromCoinQuantitiesWithStringKeysAndNormalizesPrimitives(): void
    {
        $money = Money::fromCoinQuantities([
            '100' => 1,
            '5' => 0,
            '25' => 2,
        ]);

        self::assertSame(150, $money->totalCents());
        self::assertSame([
            ['coinCents' => 25, 'quantity' => 2],
            ['coinCents' => 100, 'quantity' => 1],
        ], $money->toPrimitives());
    }

    public function testCreatesFromPrimitivesAndAddsMoneyImmutably(): void
    {
        $money = Money::fromCoins(Coin::FIVE_CENTS);
        $extraMoney = Money::fromPrimitives([
            ['coinCents' => Coin::TEN_CENTS->cents(), 'quantity' => 2],
        ]);

        $result = $money->add($extraMoney)->addCoin(Coin::TWENTY_FIVE_CENTS);

        self::assertSame(5, $money->totalCents());
        self::assertSame(50, $result->totalCents());
        self::assertSame(1, $result->quantityOf(Coin::FIVE_CENTS));
        self::assertSame(2, $result->quantityOf(Coin::TEN_CENTS));
        self::assertSame(1, $result->quantityOf(Coin::TWENTY_FIVE_CENTS));
    }

    public function testSubtractsMoneyByCoinQuantity(): void
    {
        $money = Money::fromCoins(
            Coin::FIVE_CENTS,
            Coin::FIVE_CENTS,
            Coin::TWENTY_FIVE_CENTS,
            Coin::ONE_EURO,
        );

        $result = $money->subtract(Money::fromCoins(Coin::FIVE_CENTS, Coin::TWENTY_FIVE_CENTS));

        self::assertFalse($result->isEmpty());
        self::assertSame(105, $result->totalCents());
        self::assertSame(1, $result->quantityOf(Coin::FIVE_CENTS));
        self::assertSame(0, $result->quantityOf(Coin::TWENTY_FIVE_CENTS));
    }

    public function testFailsWhenSubtractionLeavesNegativeCoinQuantity(): void
    {
        $this->expectException(InvalidCoinQuantityException::class);

        Money::empty()->subtract(Money::fromCoins(Coin::FIVE_CENTS));
    }

    public function testFailsWhenMoneyContainsUnsupportedCoin(): void
    {
        $this->expectException(InvalidCoinException::class);

        Money::fromCoinQuantities([1 => 1]);
    }

    public function testFailsWhenCoinQuantityIsNegative(): void
    {
        $this->expectException(InvalidCoinQuantityException::class);

        Money::fromCoinQuantities([Coin::FIVE_CENTS->cents() => -1]);
    }
}
