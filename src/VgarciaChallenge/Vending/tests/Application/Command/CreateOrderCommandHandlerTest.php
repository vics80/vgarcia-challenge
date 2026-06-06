<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Vending\Application\Command\CreateOrderCommand;
use App\VgarciaChallenge\Vending\Application\Command\CreateOrderCommandHandler;
use App\VgarciaChallenge\Vending\Domain\Order\Order;
use App\VgarciaChallenge\Vending\Domain\Order\OrderRepository;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use PHPUnit\Framework\TestCase;

final class CreateOrderCommandHandlerTest extends TestCase
{
    private const string VENDING_MACHINE_ID = '018f47d2-7c6a-7caa-b9d4-8b22f1c6d000';

    public function testCreatesAndSavesOrder(): void
    {
        $repository = $this->createMock(OrderRepository::class);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Order $order): bool {
                return self::VENDING_MACHINE_ID === $order->vendingMachineId()->value()
                    && ProductSelector::WATER === $order->productSelector()
                    && 65 === $order->totalAmount()->cents();
            }));

        $order = (new CreateOrderCommandHandler($repository))(new CreateOrderCommand(
            self::VENDING_MACHINE_ID,
            'WATER',
            65,
        ));

        self::assertSame(self::VENDING_MACHINE_ID, $order->vendingMachineId()->value());
        self::assertSame(ProductSelector::WATER, $order->productSelector());
        self::assertSame(65, $order->totalAmount()->cents());
    }

    public function testHandlesCreateOrderCommand(): void
    {
        self::assertSame(
            CreateOrderCommand::class,
            (new CreateOrderCommandHandler($this->createMock(OrderRepository::class)))->handles(),
        );
    }
}
