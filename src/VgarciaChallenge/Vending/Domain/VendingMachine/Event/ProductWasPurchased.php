<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\VendingMachine\Event;

use App\VgarciaChallenge\Shared\Domain\Event\AbstractDomainEvent;

final class ProductWasPurchased extends AbstractDomainEvent
{
    public function __construct(
        string $vendingMachineId,
        private readonly string $productId,
        private readonly string $productSelector,
        private readonly int $productPriceCents,
    ) {
        parent::__construct($vendingMachineId);
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function productSelector(): string
    {
        return $this->productSelector;
    }

    public function productPriceCents(): int
    {
        return $this->productPriceCents;
    }
}
