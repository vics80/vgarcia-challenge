<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Shared\Application\Command\Command;

final readonly class CreateOrderCommand implements Command
{
    public function __construct(
        private string $vendingMachineId,
        private string $productSelector,
        private int $totalAmountCents,
    ) {
    }

    public function vendingMachineId(): string
    {
        return $this->vendingMachineId;
    }

    public function productSelector(): string
    {
        return $this->productSelector;
    }

    public function totalAmountCents(): int
    {
        return $this->totalAmountCents;
    }
}
