<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\Money;

use App\VgarciaChallenge\Vending\Domain\Money\ChangeCalculator;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\CoinInventory;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\ChangeNotAvailableException;
use PHPUnit\Framework\TestCase;

final class ChangeCalculatorTest extends TestCase
{
    public function testReturnsEmptyMoneyWhenNoChangeIsNeeded(): void
    {
        $change = (new ChangeCalculator())->calculate(CoinInventory::empty(), 0);

        self::assertTrue($change->isEmpty());
    }

    public function testCalculatesChangeUsingTheFewestAvailableCoins(): void
    {
        $availableChange = CoinInventory::fromCoinQuantities([
            Coin::ONE_EURO->cents() => 2,
            Coin::TWENTY_FIVE_CENTS->cents() => 3,
            Coin::TEN_CENTS->cents() => 3,
            Coin::FIVE_CENTS->cents() => 3,
        ]);

        $change = (new ChangeCalculator())->calculate($availableChange, 135);

        self::assertSame(135, $change->totalCents());
        self::assertSame(1, $change->quantityOf(Coin::ONE_EURO));
        self::assertSame(1, $change->quantityOf(Coin::TWENTY_FIVE_CENTS));
        self::assertSame(1, $change->quantityOf(Coin::TEN_CENTS));
    }

    public function testFindsAvailableCombinationWhenLargestCoinWouldGetStuck(): void
    {
        $availableChange = CoinInventory::fromCoinQuantities([
            Coin::TWENTY_FIVE_CENTS->cents() => 1,
            Coin::TEN_CENTS->cents() => 3,
            Coin::FIVE_CENTS->cents() => 0,
        ]);

        $change = (new ChangeCalculator())->calculate($availableChange, 30);

        self::assertSame(30, $change->totalCents());
        self::assertSame(0, $change->quantityOf(Coin::TWENTY_FIVE_CENTS));
        self::assertSame(3, $change->quantityOf(Coin::TEN_CENTS));
    }

    public function testFailsWhenChangeCannotBeReturned(): void
    {
        $this->expectException(ChangeNotAvailableException::class);

        (new ChangeCalculator())->calculate(CoinInventory::fromCoinQuantities([
            Coin::TEN_CENTS->cents() => 1,
        ]), 15);
    }

    public function testFailsWhenChangeAmountIsNegative(): void
    {
        $this->expectException(ChangeNotAvailableException::class);

        (new ChangeCalculator())->calculate(CoinInventory::empty(), -5);
    }
}
