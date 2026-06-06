<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Shared\Application\Command\Command;

final readonly class DecrementProductStockCommand implements Command
{
    public function __construct(
        private string $selector,
    ) {
    }

    public function selector(): string
    {
        return $this->selector;
    }
}
