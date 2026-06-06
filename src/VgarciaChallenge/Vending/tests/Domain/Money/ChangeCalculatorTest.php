<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\Money;

use App\VgarciaChallenge\Vending\Domain\Money\ChangeCalculator;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\ChangeNotAvailableException;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use PHPUnit\Framework\TestCase;

final class ChangeCalculatorTest extends TestCase
{
    public function testReturnsEmptyMoneyWhenNoChangeIsNeeded(): void
    {
        $change = (new ChangeCalculator())->calculate(Money::empty(), 0);

        self::assertTrue($change->isEmpty());
    }

    public function testCalculatesChangeUsingTheFewestAvailableCoins(): void
    {
        $availableChange = Money::fromCoinQuantities([
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
        $availableChange = Money::fromCoinQuantities([
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

        (new ChangeCalculator())->calculate(Money::fromCoins(Coin::TEN_CENTS), 15);
    }

    public function testFailsWhenChangeAmountIsNegative(): void
    {
        $this->expectException(ChangeNotAvailableException::class);

        (new ChangeCalculator())->calculate(Money::empty(), -5);
    }
}
