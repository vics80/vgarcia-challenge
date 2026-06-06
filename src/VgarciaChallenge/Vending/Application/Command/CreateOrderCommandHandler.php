<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Shared\Application\Command\CommandHandler;
use App\VgarciaChallenge\Vending\Domain\Order\Order;
use App\VgarciaChallenge\Vending\Domain\Order\OrderId;
use App\VgarciaChallenge\Vending\Domain\Order\OrderRepository;
use App\VgarciaChallenge\Vending\Domain\Order\OrderTotalAmount;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;

final readonly class CreateOrderCommandHandler implements CommandHandler
{
    public function __construct(
        private OrderRepository $orderRepository,
    ) {
    }

    public function __invoke(CreateOrderCommand $command): Order
    {
        $order = Order::create(
            OrderId::random(),
            new VendingMachineId($command->vendingMachineId()),
            ProductSelector::from($command->productSelector()),
            OrderTotalAmount::fromCents($command->totalAmountCents()),
        );

        $this->orderRepository->save($order);

        return $order;
    }

    public function handles(): string
    {
        return CreateOrderCommand::class;
    }
}
