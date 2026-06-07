<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\Order;

use App\VgarciaChallenge\Shared\Domain\Specification\Exception\NumberMinMaxException;
use App\VgarciaChallenge\Vending\Domain\Order\Order;
use App\VgarciaChallenge\Vending\Domain\Order\OrderId;
use App\VgarciaChallenge\Vending\Domain\Order\OrderTotalAmount;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    private const string ORDER_ID = '018f47d2-7c6a-7caa-b9d4-8b22f1c6d201';

    private const string VENDING_MACHINE_ID = '018f47d2-7c6a-7caa-b9d4-8b22f1c6d000';

    public function testCreatesOrderAndExposesValues(): void
    {
        $orderId = new OrderId(self::ORDER_ID);
        $vendingMachineId = new VendingMachineId(self::VENDING_MACHINE_ID);
        $totalAmount = OrderTotalAmount::fromCents(65);

        $order = Order::create($orderId, $vendingMachineId, ProductSelector::WATER, $totalAmount);

        self::assertSame($orderId, $order->orderId());
        self::assertSame($vendingMachineId, $order->vendingMachineId());
        self::assertSame(ProductSelector::WATER, $order->productSelector());
        self::assertSame($totalAmount, $order->totalAmount());
        self::assertSame($order->createdAt(), $order->updatedAt());
    }

    public function testReconstitutesOrderWithTimestamps(): void
    {
        $createdAt = new DateTimeImmutable('2026-01-01 10:00:00');
        $updatedAt = new DateTimeImmutable('2026-01-02 10:00:00');

        $order = Order::reconstitute(
            new OrderId(self::ORDER_ID),
            new VendingMachineId(self::VENDING_MACHINE_ID),
            ProductSelector::SODA,
            OrderTotalAmount::fromCents(150),
            $createdAt,
            $updatedAt,
        );

        self::assertSame(ProductSelector::SODA, $order->productSelector());
        self::assertSame(150, $order->totalAmount()->cents());
        self::assertSame($createdAt, $order->createdAt());
        self::assertSame($updatedAt, $order->updatedAt());
    }

    public function testFailsWhenTotalAmountIsNotPositive(): void
    {
        $this->expectException(NumberMinMaxException::class);

        OrderTotalAmount::fromCents(0);
    }
}
