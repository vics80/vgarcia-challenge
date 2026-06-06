<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Repository;

use App\VgarciaChallenge\Vending\Domain\Order\Order;
use App\VgarciaChallenge\Vending\Domain\Order\OrderId;
use App\VgarciaChallenge\Vending\Domain\Order\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineOrderRepository implements OrderRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Order $order): void
    {
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }

    public function find(OrderId $orderId): ?Order
    {
        return $this->entityManager->find(Order::class, $orderId);
    }
}
