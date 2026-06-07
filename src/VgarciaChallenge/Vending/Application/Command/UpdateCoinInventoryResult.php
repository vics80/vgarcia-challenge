<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\CoinInventoryQuantity;
use App\VgarciaChallenge\Vending\Domain\Money\CoinMaxInventoryQuantity;

final readonly class UpdateCoinInventoryResult
{
    public function __construct(
        private Coin $coin,
        private int $quantity,
        private CoinInventoryQuantity $inventoryQuantity,
        private CoinMaxInventoryQuantity $maxInventoryQuantity,
    ) {
    }

    public function coin(): Coin
    {
        return $this->coin;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function inventoryQuantity(): CoinInventoryQuantity
    {
        return $this->inventoryQuantity;
    }

    public function maxInventoryQuantity(): CoinMaxInventoryQuantity
    {
        return $this->maxInventoryQuantity;
    }
}
