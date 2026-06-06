<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Repository;

use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Repository\DoctrineVendingMachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

final class DoctrineVendingMachineRepositoryTest extends TestCase
{
    public function testSavesVendingMachine(): void
    {
        $vendingMachine = $this->vendingMachine();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($vendingMachine);
        $entityManager
            ->expects(self::once())
            ->method('flush');

        (new DoctrineVendingMachineRepository($entityManager))->save($vendingMachine);
    }

    public function testFindsVendingMachineById(): void
    {
        $vendingMachineId = VendingMachineId::random();
        $vendingMachine = $this->vendingMachine($vendingMachineId);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('find')
            ->with(VendingMachine::class, $vendingMachineId)
            ->willReturn($vendingMachine);

        self::assertSame(
            $vendingMachine,
            (new DoctrineVendingMachineRepository($entityManager))->find($vendingMachineId),
        );
    }

    public function testFindsFirstVendingMachine(): void
    {
        $vendingMachine = $this->vendingMachine();
        $entityRepository = $this->createMock(EntityRepository::class);
        $entityRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with([], ['createdAt' => 'ASC'])
            ->willReturn($vendingMachine);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('getRepository')
            ->with(VendingMachine::class)
            ->willReturn($entityRepository);

        self::assertSame(
            $vendingMachine,
            (new DoctrineVendingMachineRepository($entityManager))->findFirst(),
        );
    }

    private function vendingMachine(?VendingMachineId $vendingMachineId = null): VendingMachine
    {
        return VendingMachine::create(
            $vendingMachineId ?? VendingMachineId::random(),
            Money::empty(),
            ProductInventory::empty(),
        );
    }
}
