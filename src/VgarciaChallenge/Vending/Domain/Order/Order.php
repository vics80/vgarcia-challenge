<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Order;

use App\VgarciaChallenge\Shared\Domain\AggregateRoot;
use App\VgarciaChallenge\Shared\Domain\Timestampable;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use DateTimeInterface;

class Order extends AggregateRoot
{
    use Timestampable;

    public const string ID_KEY = 'orderId';

    private function __construct(
        private OrderId $orderId,
        private VendingMachineId $vendingMachineId,
        private ProductSelector $productSelector,
        private OrderTotalAmount $totalAmount,
        ?DateTimeInterface $createdAt = null,
        ?DateTimeInterface $updatedAt = null,
    ) {
        $this->initializeTimestamps($createdAt, $updatedAt);
    }

    public static function create(
        OrderId $orderId,
        VendingMachineId $vendingMachineId,
        ProductSelector $productSelector,
        OrderTotalAmount $totalAmount,
    ): self {
        return new self($orderId, $vendingMachineId, $productSelector, $totalAmount);
    }

    public static function reconstitute(
        OrderId $orderId,
        VendingMachineId $vendingMachineId,
        ProductSelector $productSelector,
        OrderTotalAmount $totalAmount,
        ?DateTimeInterface $createdAt = null,
        ?DateTimeInterface $updatedAt = null,
    ): self {
        return new self($orderId, $vendingMachineId, $productSelector, $totalAmount, $createdAt, $updatedAt);
    }

    public function orderId(): OrderId
    {
        return $this->orderId;
    }

    public function vendingMachineId(): VendingMachineId
    {
        return $this->vendingMachineId;
    }

    public function productSelector(): ProductSelector
    {
        return $this->productSelector;
    }

    public function totalAmount(): OrderTotalAmount
    {
        return $this->totalAmount;
    }
}
