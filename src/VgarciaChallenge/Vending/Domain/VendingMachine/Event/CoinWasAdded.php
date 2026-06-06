<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\VendingMachine\Event;

use App\VgarciaChallenge\Shared\Domain\Event\AbstractDomainEvent;

final class CoinWasAdded extends AbstractDomainEvent
{
    public function __construct(
        string $vendingMachineId,
        private readonly int $coinCents,
        private readonly int $insertedMoneyTotalCents,
    ) {
        parent::__construct($vendingMachineId);
    }

    public function coinCents(): int
    {
        return $this->coinCents;
    }

    public function insertedMoneyTotalCents(): int
    {
        return $this->insertedMoneyTotalCents;
    }
}
