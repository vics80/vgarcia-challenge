<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Shared\Application\Command\Command;

final readonly class UpdateProductStockCommand implements Command
{
    public function __construct(
        private string $selector,
        private int $quantity,
    ) {
    }

    public function selector(): string
    {
        return $this->selector;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }
}
