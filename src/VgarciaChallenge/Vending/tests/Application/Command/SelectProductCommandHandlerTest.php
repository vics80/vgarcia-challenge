<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Application\Command;

use App\Tests\VgarciaChallenge\Vending\Support\Application\Query\SelectProductTestQueryBus;
use App\VgarciaChallenge\Shared\Domain\Event\DomainEvent;
use App\VgarciaChallenge\Shared\Domain\Event\DomainEventBus;
use App\VgarciaChallenge\Vending\Application\Command\SelectProductCommand;
use App\VgarciaChallenge\Vending\Application\Command\SelectProductCommandHandler;
use App\VgarciaChallenge\Vending\Domain\Money\ChangeCalculator;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\Product\Product;
use App\VgarciaChallenge\Vending\Domain\Product\ProductId;
use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\Product\ProductStockQuantity;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Event\ProductWasPurchased;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\VendingMachineNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;
use PHPUnit\Framework\TestCase;

final class SelectProductCommandHandlerTest extends TestCase
{
    public function testSelectsProductAndPublishesProductPurchasedEvent(): void
    {
        $product = $this->product(ProductSelector::WATER);
        $vendingMachine = VendingMachine::reconstitute(
            VendingMachineId::random(),
            Money::fromCoins(Coin::ONE_EURO),
            Money::fromCoins(Coin::ONE_EURO, Coin::TWENTY_FIVE_CENTS, Coin::TEN_CENTS),
            ProductInventory::fromProducts($product),
        );
        $repository = $this->createMock(VendingMachineRepository::class);
        $repository
            ->expects(self::once())
            ->method('findFirst')
            ->willReturn($vendingMachine);
        $domainEventBus = $this->createMock(DomainEventBus::class);
        $domainEventBus
            ->expects(self::once())
            ->method('publish')
            ->with(self::callback(static function (DomainEvent $event) use ($product): bool {
                return $event instanceof ProductWasPurchased
                    && $product->productId()->value() === $event->productId()
                    && 'WATER' === $event->productSelector()
                    && 65 === $event->productPriceCents();
            }));

        $handler = new SelectProductCommandHandler(
            new SelectProductTestQueryBus($product),
            $repository,
            new ChangeCalculator(),
            $domainEventBus,
        );

        $result = $handler(new SelectProductCommand('WATER'));

        self::assertSame($product, $result->product());
        self::assertSame(35, $result->returnedChange()->totalCents());
    }

    public function testHandlesSelectProductCommand(): void
    {
        $handler = new SelectProductCommandHandler(
            new SelectProductTestQueryBus($this->product(ProductSelector::WATER)),
            $this->createMock(VendingMachineRepository::class),
            new ChangeCalculator(),
            $this->createMock(DomainEventBus::class),
        );

        self::assertSame(SelectProductCommand::class, $handler->handles());
    }

    public function testFailsWhenThereIsNoVendingMachineAfterProductValidation(): void
    {
        $repository = $this->createMock(VendingMachineRepository::class);
        $repository
            ->expects(self::once())
            ->method('findFirst')
            ->willReturn(null);
        $domainEventBus = $this->createMock(DomainEventBus::class);
        $domainEventBus
            ->expects(self::never())
            ->method('publish');

        $handler = new SelectProductCommandHandler(
            new SelectProductTestQueryBus($this->product(ProductSelector::WATER)),
            $repository,
            new ChangeCalculator(),
            $domainEventBus,
        );

        $this->expectException(VendingMachineNotFoundException::class);

        $handler(new SelectProductCommand('WATER'));
    }

    private function product(ProductSelector $selector): Product
    {
        return Product::create(
            ProductId::random(),
            $selector->defaultName(),
            $selector,
            $selector->defaultPrice(),
            new ProductStockQuantity(10),
        );
    }
}
