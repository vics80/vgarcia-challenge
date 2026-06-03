<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\VendingMachine;

interface VendingMachineRepository
{
    public function save(VendingMachine $vendingMachine): void;

    public function find(VendingMachineId $vendingMachineId): ?VendingMachine;
}
