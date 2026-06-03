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
