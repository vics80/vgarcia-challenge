<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Application\Event;

use App\VgarciaChallenge\Vending\Application\Command\CreateOrderCommandHandler;
use App\VgarciaChallenge\Vending\Application\Command\DecrementProductStockCommandHandler;
use App\VgarciaChallenge\Vending\Application\Command\ReturnChangeCommandHandler;
use App\VgarciaChallenge\Vending\Application\Event\CreateOrderOnProductWasPurchased;
use App\VgarciaChallenge\Vending\Application\Event\DecrementProductStockOnProductWasPurchased;
use App\VgarciaChallenge\Vending\Application\Event\ReturnChangeOnProductWasPurchased;
use App\VgarciaChallenge\Vending\Domain\Money\ChangeCalculator;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\Order\Order;
use App\VgarciaChallenge\Vending\Domain\Order\OrderRepository;
use App\VgarciaChallenge\Vending\Domain\Product\Product;
use App\VgarciaChallenge\Vending\Domain\Product\ProductId;
use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\Product\ProductStockQuantity;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Event\ProductWasPurchased;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;
use PHPUnit\Framework\TestCase;

final class ProductWasPurchasedSubscribersTest extends TestCase
{
    public function testCreateOrderSubscriberCreatesOrder(): void
    {
        $orderRepository = $this->createMock(OrderRepository::class);
        $orderRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Order $order): bool {
                return '018f47d2-7c6a-7caa-b9d4-8b22f1c6d000' === $order->vendingMachineId()->value()
                    && ProductSelector::WATER === $order->productSelector()
                    && 65 === $order->totalAmount()->cents();
            }));
        $subscriber = new CreateOrderOnProductWasPurchased(new CreateOrderCommandHandler($orderRepository));

        $subscriber($this->event());

        self::assertSame(ProductWasPurchased::class, $subscriber->subscribedTo());
    }

    public function testDecrementProductStockSubscriberDispatchesCommand(): void
    {
        $product = $this->product(ProductSelector::WATER);
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            Money::empty(),
            ProductInventory::fromProducts($product),
        );
        $repository = $this->repository($vendingMachine);
        $repository
            ->expects(self::once())
            ->method('save');
        $subscriber = new DecrementProductStockOnProductWasPurchased(
            new DecrementProductStockCommandHandler($repository),
        );

        $subscriber($this->event());

        self::assertSame(ProductWasPurchased::class, $subscriber->subscribedTo());
        self::assertSame(1, $product->stockQuantity()->value());
    }

    public function testReturnChangeSubscriberDispatchesCommand(): void
    {
        $vendingMachine = VendingMachine::reconstitute(
            VendingMachineId::random(),
            Money::fromCoins(Coin::ONE_EURO),
            Money::fromCoins(Coin::ONE_EURO, Coin::TWENTY_FIVE_CENTS, Coin::TEN_CENTS),
            ProductInventory::empty(),
        );
        $repository = $this->repository($vendingMachine);
        $repository
            ->expects(self::once())
            ->method('save');
        $subscriber = new ReturnChangeOnProductWasPurchased(
            new ReturnChangeCommandHandler($repository, new ChangeCalculator()),
        );

        $subscriber($this->event());

        self::assertSame(ProductWasPurchased::class, $subscriber->subscribedTo());
        self::assertSame(0, $vendingMachine->insertedMoney()->totalCents());
        self::assertSame(100, $vendingMachine->availableChange()->totalCents());
    }

    private function event(): ProductWasPurchased
    {
        return new ProductWasPurchased(
            '018f47d2-7c6a-7caa-b9d4-8b22f1c6d000',
            'product-id',
            'WATER',
            65,
        );
    }

    private function repository(VendingMachine $vendingMachine): VendingMachineRepository
    {
        $repository = $this->createMock(VendingMachineRepository::class);
        $repository
            ->method('findFirst')
            ->willReturn($vendingMachine);

        return $repository;
    }

    private function product(ProductSelector $selector): Product
    {
        return Product::create(
            ProductId::random(),
            $selector->defaultName(),
            $selector,
            $selector->defaultPrice(),
            new ProductStockQuantity(2),
        );
    }
}
