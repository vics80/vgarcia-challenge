<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Money;

use App\VgarciaChallenge\Vending\Domain\Money\Exception\InvalidCoinException;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\InvalidCoinQuantityException;

use function array_key_exists;
use function array_map;
use function array_values;

final class Money
{
    /** @var array<int, int> */
    private array $coinQuantities;

    /** @param array<int, int> $coinQuantities */
    private function __construct(array $coinQuantities)
    {
        $this->coinQuantities = $this->normalize($coinQuantities);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public static function fromCoins(Coin ...$coins): self
    {
        $coinQuantities = [];

        foreach ($coins as $coin) {
            $coinQuantities[$coin->cents()] = ($coinQuantities[$coin->cents()] ?? 0) + 1;
        }

        return new self($coinQuantities);
    }

    /** @param array<int|string, int> $coinQuantities */
    public static function fromCoinQuantities(array $coinQuantities): self
    {
        $normalized = [];

        foreach ($coinQuantities as $coinCents => $quantity) {
            $normalized[(int) $coinCents] = $quantity;
        }

        return new self($normalized);
    }

    /** @param list<array{coinCents:int,quantity:int}> $coins */
    public static function fromPrimitives(array $coins): self
    {
        $coinQuantities = [];

        foreach ($coins as $coin) {
            $coinQuantities[$coin['coinCents']] = $coin['quantity'];
        }

        return new self($coinQuantities);
    }

    public function totalCents(): int
    {
        $total = 0;

        foreach ($this->coinQuantities as $coinCents => $quantity) {
            $total += $coinCents * $quantity;
        }

        return $total;
    }

    public function decimalString(): string
    {
        return sprintf('%d.%02d', intdiv($this->totalCents(), 100), $this->totalCents() % 100);
    }

    public function quantityOf(Coin $coin): int
    {
        return $this->coinQuantities[$coin->cents()] ?? 0;
    }

    public function addCoin(Coin $coin): self
    {
        return $this->add(self::fromCoins($coin));
    }

    public function add(self $money): self
    {
        $coinQuantities = $this->coinQuantities;

        foreach ($money->coinQuantities as $coinCents => $quantity) {
            $coinQuantities[$coinCents] = ($coinQuantities[$coinCents] ?? 0) + $quantity;
        }

        return new self($coinQuantities);
    }

    /** @return list<array{coinCents:int,quantity:int}> */
    public function toPrimitives(): array
    {
        return array_values(array_map(
            static fn (int $coinCents, int $quantity): array => [
                'coinCents' => $coinCents,
                'quantity' => $quantity,
            ],
            array_keys($this->coinQuantities),
            $this->coinQuantities,
        ));
    }

    /** @param array<int, int> $coinQuantities */
    private function normalize(array $coinQuantities): array
    {
        $normalized = [];
        $validCoins = $this->validCoinValues();

        foreach ($coinQuantities as $coinCents => $quantity) {
            if (!array_key_exists($coinCents, $validCoins)) {
                throw InvalidCoinException::fromCents($coinCents);
            }

            if ($quantity < 0) {
                throw InvalidCoinQuantityException::fromQuantity($quantity);
            }

            if (0 === $quantity) {
                continue;
            }

            $normalized[$coinCents] = $quantity;
        }

        ksort($normalized);

        return $normalized;
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
