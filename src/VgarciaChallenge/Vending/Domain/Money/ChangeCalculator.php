<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Money;

use App\VgarciaChallenge\Vending\Domain\Money\Exception\ChangeNotAvailableException;

use function array_sum;

final class ChangeCalculator
{
    /** @var list<Coin> */
    private const array COINS_BY_DESCENDING_VALUE = [
        Coin::ONE_EURO,
        Coin::TWENTY_FIVE_CENTS,
        Coin::TEN_CENTS,
        Coin::FIVE_CENTS,
    ];

    public function calculate(Money $availableChange, int $amountCents): Money
    {
        if (0 === $amountCents) {
            return Money::empty();
        }

        if ($amountCents < 0) {
            throw ChangeNotAvailableException::forAmount($amountCents);
        }

        /** @var array<int, array<int, int>> $bestCombinations */
        $bestCombinations = [0 => []];

        foreach (self::COINS_BY_DESCENDING_VALUE as $coin) {
            for ($usedCoins = 0; $usedCoins < $availableChange->quantityOf($coin); ++$usedCoins) {
                $nextCombinations = $bestCombinations;

                foreach ($bestCombinations as $currentAmount => $coinQuantities) {
                    $newAmount = $currentAmount + $coin->cents();

                    if ($newAmount > $amountCents) {
                        continue;
                    }

                    $newCoinQuantities = $coinQuantities;
                    $newCoinQuantities[$coin->cents()] = ($newCoinQuantities[$coin->cents()] ?? 0) + 1;

                    if (
                        !array_key_exists($newAmount, $nextCombinations)
                        || $this->coinCount($newCoinQuantities) < $this->coinCount($nextCombinations[$newAmount])
                    ) {
                        $nextCombinations[$newAmount] = $newCoinQuantities;
                    }
                }

                $bestCombinations = $nextCombinations;
            }
        }

        if (!array_key_exists($amountCents, $bestCombinations)) {
            throw ChangeNotAvailableException::forAmount($amountCents);
        }

        return Money::fromCoinQuantities($bestCombinations[$amountCents]);
    }

    /** @param array<int, int> $coinQuantities */
    private function coinCount(array $coinQuantities): int
    {
        return array_sum($coinQuantities);
    }
}
