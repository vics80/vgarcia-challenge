<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Repository;

use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineVendingMachineRepository implements VendingMachineRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(VendingMachine $vendingMachine): void
    {
        $this->entityManager->persist($vendingMachine);
        $this->entityManager->flush();
    }

    public function find(VendingMachineId $vendingMachineId): ?VendingMachine
    {
        return $this->entityManager->find(VendingMachine::class, $vendingMachineId);
    }

    public function findFirst(): ?VendingMachine
    {
        return $this->entityManager
            ->getRepository(VendingMachine::class)
            ->findOneBy([], ['createdAt' => 'ASC']);
    }
}
