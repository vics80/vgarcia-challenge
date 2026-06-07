<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Money;

use App\VgarciaChallenge\Vending\Domain\Money\Exception\CoinInventoryLimitExceededException;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\CoinInventoryNotEnoughException;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\InvalidCoinException;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\InvalidCoinInventoryAdjustmentException;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\InvalidCoinQuantityException;

use function abs;
use function array_key_exists;

final class CoinInventory
{
    /** @var array<int, int> */
    private array $coinQuantities;

    /** @var array<int, int> */
    private array $coinMaxQuantities;

    /**
     * @param array<int, int> $coinQuantities
     * @param array<int, int> $coinMaxQuantities
     */
    private function __construct(array $coinQuantities, array $coinMaxQuantities)
    {
        $this->coinMaxQuantities = $this->normalizeMaxQuantities($coinMaxQuantities);
        $this->coinQuantities = $this->normalizeQuantities($coinQuantities);
    }

    public static function empty(): self
    {
        return new self([], []);
    }

    public static function fromMoney(Money $money): self
    {
        return self::fromPrimitives($money->toPrimitives());
    }

    /** @param array<int|string, int> $coinQuantities */
    public static function fromCoinQuantities(array $coinQuantities): self
    {
        $normalized = [];

        foreach ($coinQuantities as $coinCents => $quantity) {
            $normalized[(int) $coinCents] = $quantity;
        }

        return new self($normalized, []);
    }

    /**
     * @param array<int|string, int> $coinQuantities
     * @param array<int|string, int> $coinMaxQuantities
     */
    public static function fromCoinQuantitiesWithMax(array $coinQuantities, array $coinMaxQuantities): self
    {
        $normalizedQuantities = [];
        $normalizedMaxQuantities = [];

        foreach ($coinQuantities as $coinCents => $quantity) {
            $normalizedQuantities[(int) $coinCents] = $quantity;
        }

        foreach ($coinMaxQuantities as $coinCents => $quantity) {
            $normalizedMaxQuantities[(int) $coinCents] = $quantity;
        }

        return new self($normalizedQuantities, $normalizedMaxQuantities);
    }

    /** @param list<array{coinCents:int,quantity:int,maxQuantity?:int}> $coins */
    public static function fromPrimitives(array $coins): self
    {
        $coinQuantities = [];
        $coinMaxQuantities = [];

        foreach ($coins as $coin) {
            $coinQuantities[$coin['coinCents']] = $coin['quantity'];

            if (array_key_exists('maxQuantity', $coin)) {
                $coinMaxQuantities[$coin['coinCents']] = $coin['maxQuantity'];
            }
        }

        return new self($coinQuantities, $coinMaxQuantities);
    }

    public function totalCents(): int
    {
        $total = 0;

        foreach ($this->coinQuantities as $coinCents => $quantity) {
            $total += $coinCents * $quantity;
        }

        return $total;
    }

    public function quantityOf(Coin $coin): int
    {
        return $this->coinQuantities[$coin->cents()];
    }

    public function inventoryQuantityOf(Coin $coin): CoinInventoryQuantity
    {
        return new CoinInventoryQuantity($this->quantityOf($coin));
    }

    public function maxQuantityOf(Coin $coin): CoinMaxInventoryQuantity
    {
        return new CoinMaxInventoryQuantity($this->coinMaxQuantities[$coin->cents()]);
    }

    public function isEmpty(): bool
    {
        return 0 === $this->totalCents();
    }

    public function addCoin(Coin $coin): self
    {
        return $this->changeCoinQuantity($coin, 1);
    }

    public function subtract(Money $money): self
    {
        $inventory = $this;

        foreach ($money->toPrimitives() as $coin) {
            $inventory = $inventory->changeCoinQuantity(Coin::from($coin['coinCents']), -$coin['quantity']);
        }

        return $inventory;
    }

    public function changeCoinQuantity(Coin $coin, int $quantity): self
    {
        $this->ensureAdjustmentIsNotZero($coin, $quantity);

        $coinQuantities = $this->coinQuantities;
        $newQuantity = $coinQuantities[$coin->cents()] + $quantity;

        $this->ensureCoinQuantityIsAvailable($coin, $quantity, $newQuantity);
        $this->ensureMaxQuantityIsNotExceeded($coin, $newQuantity);

        $coinQuantities[$coin->cents()] = $newQuantity;

        return new self($coinQuantities, $this->coinMaxQuantities);
    }

    /** @return list<array{coinCents:int,quantity:int,maxQuantity:int}> */
    public function toPrimitives(): array
    {
        $primitives = [];

        foreach (Coin::cases() as $coin) {
            $quantity = $this->quantityOf($coin);
            $maxQuantity = $this->maxQuantityOf($coin)->value();

            if (0 === $quantity && $maxQuantity === $coin->defaultMaxInventoryQuantity()->value()) {
                continue;
            }

            $primitives[] = [
                'coinCents' => $coin->cents(),
                'quantity' => $quantity,
                'maxQuantity' => $maxQuantity,
            ];
        }

        return $primitives;
    }

    /** @param array<int, int> $coinMaxQuantities */
    private function normalizeMaxQuantities(array $coinMaxQuantities): array
    {
        $normalized = $this->defaultMaxQuantities();
        $validCoins = $this->validCoinValues();

        foreach ($coinMaxQuantities as $coinCents => $quantity) {
            $this->ensureCoinIsAccepted($coinCents, $validCoins);
            $this->ensureMaxQuantityIsPositive($quantity);

            $normalized[$coinCents] = $quantity;
        }

        return $normalized;
    }

    /** @param array<int, int> $coinQuantities */
    private function normalizeQuantities(array $coinQuantities): array
    {
        $normalized = $this->emptyQuantities();
        $validCoins = $this->validCoinValues();

        foreach ($coinQuantities as $coinCents => $quantity) {
            $this->ensureCoinIsAccepted($coinCents, $validCoins);
            $this->ensureQuantityIsNotNegative($quantity);
            $this->ensureMaxQuantityIsNotExceeded(Coin::from($coinCents), $quantity);

            $normalized[$coinCents] = $quantity;
        }

        return $normalized;
    }

    /** @param array<int, true> $validCoins */
    private function ensureCoinIsAccepted(int $coinCents, array $validCoins): void
    {
        if (!array_key_exists($coinCents, $validCoins)) {
            throw InvalidCoinException::fromCents($coinCents);
        }
    }

    private function ensureQuantityIsNotNegative(int $quantity): void
    {
        if ($quantity < 0) {
            throw InvalidCoinQuantityException::fromQuantity($quantity);
        }
    }

    private function ensureMaxQuantityIsPositive(int $quantity): void
    {
        if ($quantity < CoinMaxInventoryQuantity::MIN) {
            throw InvalidCoinQuantityException::fromQuantity($quantity);
        }
    }

    private function ensureAdjustmentIsNotZero(Coin $coin, int $quantity): void
    {
        if (0 === $quantity) {
            throw InvalidCoinInventoryAdjustmentException::forCoin($coin);
        }
    }

    private function ensureCoinQuantityIsAvailable(Coin $coin, int $quantity, int $newQuantity): void
    {
        if ($newQuantity < 0) {
            throw CoinInventoryNotEnoughException::forCoin($coin, abs($quantity), $this->quantityOf($coin));
        }
    }

    private function ensureMaxQuantityIsNotExceeded(Coin $coin, int $quantity): void
    {
        if ($quantity > $this->maxQuantityOf($coin)->value()) {
            throw CoinInventoryLimitExceededException::forCoin(
                $coin,
                $quantity,
                $this->maxQuantityOf($coin)->value(),
            );
        }
    }

    /** @return array<int, int> */
    private function emptyQuantities(): array
    {
        $quantities = [];

        foreach (Coin::cases() as $coin) {
            $quantities[$coin->cents()] = 0;
        }

        return $quantities;
    }

    /** @return array<int, int> */
    private function defaultMaxQuantities(): array
    {
        $quantities = [];

        foreach (Coin::cases() as $coin) {
            $quantities[$coin->cents()] = $coin->defaultMaxInventoryQuantity()->value();
        }

        return $quantities;
    }

    /** @return array<int, true> */
    private function validCoinValues(): array
    {
        return [
            Coin::FIVE_CENTS->cents() => true,
            Coin::TEN_CENTS->cents() => true,
            Coin::TWENTY_FIVE_CENTS->cents() => true,
            Coin::ONE_EURO->cents() => true,
        ];
    }
}
