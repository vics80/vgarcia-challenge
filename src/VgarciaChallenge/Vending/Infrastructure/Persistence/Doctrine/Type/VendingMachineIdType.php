<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;

final class VendingMachineIdType extends UuidValueObjectType
{
    public const string NAME = 'vending_machine_id';

    protected function valueObjectClass(): string
    {
        return VendingMachineId::class;
    }
}
