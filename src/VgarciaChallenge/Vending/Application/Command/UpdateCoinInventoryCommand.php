<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Shared\Application\Command\Command;

final readonly class UpdateCoinInventoryCommand implements Command
{
    public function __construct(
        private string $coin,
        private int $quantity,
    ) {
    }

    public function coin(): string
    {
        return $this->coin;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }
}
