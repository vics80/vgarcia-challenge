<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Shared\Application\Command\Command;

final readonly class InsertCoinCommand implements Command
{
    public function __construct(
        private string $coin,
    ) {
    }

    public function coin(): string
    {
        return $this->coin;
    }
}
