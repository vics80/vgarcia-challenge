<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Repository;

use App\VgarciaChallenge\Vending\Domain\Order\Order;
use App\VgarciaChallenge\Vending\Domain\Order\OrderId;
use App\VgarciaChallenge\Vending\Domain\Order\OrderTotalAmount;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Repository\DoctrineOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class DoctrineOrderRepositoryTest extends TestCase
{
    public function testSavesOrder(): void
    {
        $order = $this->order();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($order);
        $entityManager
            ->expects(self::once())
            ->method('flush');

        (new DoctrineOrderRepository($entityManager))->save($order);
    }

    public function testFindsOrderById(): void
    {
        $orderId = OrderId::random();
        $order = $this->order($orderId);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('find')
            ->with(Order::class, $orderId)
            ->willReturn($order);

        self::assertSame($order, (new DoctrineOrderRepository($entityManager))->find($orderId));
    }

    private function order(?OrderId $orderId = null): Order
    {
        return Order::create(
            $orderId ?? OrderId::random(),
            VendingMachineId::random(),
            ProductSelector::WATER,
            OrderTotalAmount::fromCents(65),
        );
    }
}
