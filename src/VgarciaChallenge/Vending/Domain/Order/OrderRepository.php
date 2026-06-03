<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Order;

interface OrderRepository
{
    public function save(Order $order): void;

    public function find(OrderId $orderId): ?Order;
}
