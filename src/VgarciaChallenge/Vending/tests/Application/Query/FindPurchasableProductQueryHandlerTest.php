<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Application\Query;

use App\VgarciaChallenge\Vending\Application\Query\FindPurchasableProductQuery;
use App\VgarciaChallenge\Vending\Application\Query\FindPurchasableProductQueryHandler;
use App\VgarciaChallenge\Vending\Domain\Money\ChangeCalculator;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\ChangeNotAvailableException;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\Product\Exception\InsufficientMoneyException;
use App\VgarciaChallenge\Vending\Domain\Product\Exception\ProductNotFoundException;
use App\VgarciaChallenge\Vending\Domain\Product\Exception\ProductOutOfStockException;
use App\VgarciaChallenge\Vending\Domain\Product\Product;
use App\VgarciaChallenge\Vending\Domain\Product\ProductId;
use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\Product\ProductStockQuantity;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\VendingMachineNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;
use PHPUnit\Framework\TestCase;

final class FindPurchasableProductQueryHandlerTest extends TestCase
{
    public function testFindsPurchasableProduct(): void
    {
        $product = $this->product(ProductSelector::WATER, 10);
        $handler = new FindPurchasableProductQueryHandler(
            $this->repository($this->vendingMachine(
                Money::fromCoins(Coin::ONE_EURO),
                Money::fromCoins(Coin::ONE_EURO, Coin::TWENTY_FIVE_CENTS, Coin::TEN_CENTS),
                ProductInventory::fromProducts($product),
            )),
            new ChangeCalculator(),
        );

        self::assertSame($product, $handler(new FindPurchasableProductQuery('WATER')));
    }

    public function testHandlesFindPurchasableProductQuery(): void
    {
        $handler = new FindPurchasableProductQueryHandler($this->repository(null), new ChangeCalculator());

        self::assertSame(FindPurchasableProductQuery::class, $handler->handles());
    }

    public function testFailsWhenThereIsNoVendingMachine(): void
    {
        $handler = new FindPurchasableProductQueryHandler($this->repository(null), new ChangeCalculator());

        $this->expectException(VendingMachineNotFoundException::class);

        $handler(new FindPurchasableProductQuery('WATER'));
    }

    public function testFailsWhenSelectorIsUnknown(): void
    {
        $handler = new FindPurchasableProductQueryHandler(
            $this->repository($this->vendingMachine(Money::empty(), Money::empty(), ProductInventory::empty())),
            new ChangeCalculator(),
        );

        $this->expectException(ProductNotFoundException::class);

        $handler(new FindPurchasableProductQuery('UNKNOWN'));
    }

    public function testFailsWhenProductIsNotInInventory(): void
    {
        $handler = new FindPurchasableProductQueryHandler(
            $this->repository($this->vendingMachine(Money::empty(), Money::empty(), ProductInventory::empty())),
            new ChangeCalculator(),
        );

        $this->expectException(ProductNotFoundException::class);

        $handler(new FindPurchasableProductQuery('WATER'));
    }

    public function testFailsWhenProductIsOutOfStock(): void
    {
        $handler = new FindPurchasableProductQueryHandler(
            $this->repository($this->vendingMachine(
                Money::fromCoins(Coin::ONE_EURO),
                Money::fromCoins(Coin::ONE_EURO),
                ProductInventory::fromProducts($this->product(ProductSelector::WATER, 0)),
            )),
            new ChangeCalculator(),
        );

        $this->expectException(ProductOutOfStockException::class);

        $handler(new FindPurchasableProductQuery('WATER'));
    }

    public function testFailsWhenInsertedMoneyIsNotEnough(): void
    {
        $handler = new FindPurchasableProductQueryHandler(
            $this->repository($this->vendingMachine(
                Money::fromCoins(Coin::FIVE_CENTS),
                Money::fromCoins(Coin::FIVE_CENTS),
                ProductInventory::fromProducts($this->product(ProductSelector::WATER, 10)),
            )),
            new ChangeCalculator(),
        );

        $this->expectException(InsufficientMoneyException::class);

        $handler(new FindPurchasableProductQuery('WATER'));
    }

    public function testFailsWhenChangeCannotBeReturned(): void
    {
        $handler = new FindPurchasableProductQueryHandler(
            $this->repository($this->vendingMachine(
                Money::fromCoins(Coin::ONE_EURO),
                Money::fromCoins(Coin::ONE_EURO),
                ProductInventory::fromProducts($this->product(ProductSelector::WATER, 10)),
            )),
            new ChangeCalculator(),
        );

        $this->expectException(ChangeNotAvailableException::class);

        $handler(new FindPurchasableProductQuery('WATER'));
    }

    private function repository(?VendingMachine $vendingMachine): VendingMachineRepository
    {
        $repository = $this->createMock(VendingMachineRepository::class);
        $repository
            ->method('findFirst')
            ->willReturn($vendingMachine);

        return $repository;
    }

    private function vendingMachine(
        Money $insertedMoney,
        Money $availableChange,
        ProductInventory $productInventory,
    ): VendingMachine {
        return VendingMachine::reconstitute(
            VendingMachineId::random(),
            $insertedMoney,
            $availableChange,
            $productInventory,
        );
    }

    private function product(ProductSelector $selector, int $stockQuantity): Product
    {
        return Product::create(
            ProductId::random(),
            $selector->defaultName(),
            $selector,
            $selector->defaultPrice(),
            new ProductStockQuantity($stockQuantity),
        );
    }
}
