<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\VendingMachine;

use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\Product\Exception\ProductNotFoundException;
use App\VgarciaChallenge\Vending\Domain\Product\Exception\ProductStockLimitExceededException;
use App\VgarciaChallenge\Vending\Domain\Product\Exception\ProductStockNotEnoughException;
use App\VgarciaChallenge\Vending\Domain\Product\Product;
use App\VgarciaChallenge\Vending\Domain\Product\ProductId;
use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\Product\ProductStockQuantity;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Event\CoinWasAdded;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Event\ProductWasPurchased;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\CoinsNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class VendingMachineTest extends TestCase
{
    public function testCreatesVendingMachineAndExposesValues(): void
    {
        $id = VendingMachineId::random();
        $availableChange = Money::fromCoins(Coin::ONE_EURO);
        $productInventory = ProductInventory::empty();

        $vendingMachine = VendingMachine::create($id, $availableChange, $productInventory);

        self::assertSame($id, $vendingMachine->vendingMachineId());
        self::assertSame(0, $vendingMachine->insertedMoney()->totalCents());
        self::assertSame($availableChange, $vendingMachine->availableChange());
        self::assertSame($productInventory, $vendingMachine->productInventory());
        self::assertSame($vendingMachine->createdAt(), $vendingMachine->updatedAt());
    }

    public function testReconstitutesVendingMachineWithStateAndTimestamps(): void
    {
        $createdAt = new DateTimeImmutable('2026-01-01 10:00:00');
        $updatedAt = new DateTimeImmutable('2026-01-02 10:00:00');
        $productInventory = ProductInventory::empty();

        $vendingMachine = VendingMachine::reconstitute(
            VendingMachineId::random(),
            Money::fromCoins(Coin::FIVE_CENTS),
            Money::fromCoins(Coin::TEN_CENTS),
            $productInventory,
            $createdAt,
            $updatedAt,
        );

        self::assertSame(5, $vendingMachine->insertedMoney()->totalCents());
        self::assertSame(10, $vendingMachine->availableChange()->totalCents());
        self::assertSame($productInventory, $vendingMachine->productInventory());
        self::assertSame($createdAt, $vendingMachine->createdAt());
        self::assertSame($updatedAt, $vendingMachine->updatedAt());
    }

    public function testInsertCoinAddsCoinToInsertedMoneyAndAvailableChange(): void
    {
        $availableChange = Money::fromCoinQuantities([
            Coin::FIVE_CENTS->cents() => 10,
            Coin::TEN_CENTS->cents() => 10,
        ]);
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            $availableChange,
            ProductInventory::empty(),
        );

        $vendingMachine->insertCoin(Coin::TWENTY_FIVE_CENTS);

        self::assertSame(25, $vendingMachine->insertedMoney()->totalCents());
        self::assertSame(175, $vendingMachine->availableChange()->totalCents());
    }

    public function testInsertCoinRecordsDomainEvent(): void
    {
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            Money::empty(),
            ProductInventory::empty(),
        );

        $vendingMachine->insertCoin(Coin::ONE_EURO);

        $events = $vendingMachine->pullDomainEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(CoinWasAdded::class, $events[0]);
        self::assertSame(100, $events[0]->coinCents());
        self::assertSame(100, $events[0]->insertedMoneyTotalCents());
        self::assertSame($vendingMachine->vendingMachineId()->value(), $events[0]->aggregateId());
        self::assertSame([], $vendingMachine->pullDomainEvents());
    }

    public function testInsertCoinTouchesUpdatedAt(): void
    {
        $updatedAt = new DateTimeImmutable('2026-01-01 10:00:00');
        $vendingMachine = VendingMachine::reconstitute(
            VendingMachineId::random(),
            Money::empty(),
            Money::empty(),
            ProductInventory::empty(),
            new DateTimeImmutable('2026-01-01 09:00:00'),
            $updatedAt,
        );

        $vendingMachine->insertCoin(Coin::FIVE_CENTS);

        self::assertNotSame($updatedAt, $vendingMachine->updatedAt());
    }

    public function testReturnsInsertedMoneyAndClearsInsertedCoins(): void
    {
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            Money::fromCoins(Coin::ONE_EURO),
            ProductInventory::empty(),
        );
        $vendingMachine->insertCoin(Coin::FIVE_CENTS);
        $vendingMachine->insertCoin(Coin::FIVE_CENTS);
        $vendingMachine->insertCoin(Coin::TWENTY_FIVE_CENTS);

        $returnedMoney = $vendingMachine->returnInsertedMoney();

        self::assertSame(35, $returnedMoney->totalCents());
        self::assertSame(2, $returnedMoney->quantityOf(Coin::FIVE_CENTS));
        self::assertSame(1, $returnedMoney->quantityOf(Coin::TWENTY_FIVE_CENTS));
        self::assertSame(0, $vendingMachine->insertedMoney()->totalCents());
        self::assertSame(100, $vendingMachine->availableChange()->totalCents());
    }

    public function testReturnInsertedMoneyTouchesUpdatedAt(): void
    {
        $updatedAt = new DateTimeImmutable('2026-01-01 10:00:00');
        $vendingMachine = VendingMachine::reconstitute(
            VendingMachineId::random(),
            Money::fromCoins(Coin::TEN_CENTS),
            Money::fromCoins(Coin::TEN_CENTS),
            ProductInventory::empty(),
            new DateTimeImmutable('2026-01-01 09:00:00'),
            $updatedAt,
        );

        $vendingMachine->returnInsertedMoney();

        self::assertNotSame($updatedAt, $vendingMachine->updatedAt());
    }

    public function testFailsWhenThereAreNoInsertedCoinsToReturn(): void
    {
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            Money::empty(),
            ProductInventory::empty(),
        );

        $this->expectException(CoinsNotFoundException::class);
        $this->expectExceptionMessage('No inserted coins were found to return.');

        $vendingMachine->returnInsertedMoney();
    }

    public function testPurchaseProductRecordsDomainEvent(): void
    {
        $product = $this->product(ProductSelector::WATER, 10);
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            Money::empty(),
            ProductInventory::fromProducts($product),
        );

        $vendingMachine->purchaseProduct($product);

        $events = $vendingMachine->pullDomainEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(ProductWasPurchased::class, $events[0]);
        self::assertSame($vendingMachine->vendingMachineId()->value(), $events[0]->aggregateId());
        self::assertSame($product->productId()->value(), $events[0]->productId());
        self::assertSame('WATER', $events[0]->productSelector());
        self::assertSame(65, $events[0]->productPriceCents());
    }

    public function testDecrementsProductStock(): void
    {
        $product = $this->product(ProductSelector::WATER, 2);
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            Money::empty(),
            ProductInventory::fromProducts($product),
        );
        $initialInventory = $vendingMachine->productInventory();

        $vendingMachine->decrementProductStock(ProductSelector::WATER);

        self::assertSame(1, $product->stockQuantity()->value());
        self::assertNotSame($initialInventory, $vendingMachine->productInventory());
        self::assertSame(1, $vendingMachine->productInventory()->find(ProductSelector::WATER)?->stockQuantity()->value());
    }

    public function testFailsWhenDecrementingMissingProductStock(): void
    {
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            Money::empty(),
            ProductInventory::empty(),
        );

        $this->expectException(ProductNotFoundException::class);

        $vendingMachine->decrementProductStock(ProductSelector::WATER);
    }

    public function testChangesProductStock(): void
    {
        $product = $this->product(ProductSelector::WATER, 10);
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            Money::empty(),
            ProductInventory::fromProducts($product),
        );
        $initialInventory = $vendingMachine->productInventory();

        $updatedProduct = $vendingMachine->changeProductStock(ProductSelector::WATER, 5);

        self::assertSame(15, $updatedProduct->stockQuantity()->value());
        self::assertNotSame($initialInventory, $vendingMachine->productInventory());
        self::assertSame(15, $vendingMachine->productInventory()->find(ProductSelector::WATER)?->stockQuantity()->value());
    }

    public function testFailsWhenRemovingMoreProductStockThanAvailable(): void
    {
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            Money::empty(),
            ProductInventory::fromProducts($this->product(ProductSelector::WATER, 2)),
        );

        $this->expectException(ProductStockNotEnoughException::class);

        $vendingMachine->changeProductStock(ProductSelector::WATER, -3);
    }

    public function testFailsWhenAddingProductStockExceedsMaxStock(): void
    {
        $vendingMachine = VendingMachine::create(
            VendingMachineId::random(),
            Money::empty(),
            ProductInventory::fromProducts($this->product(ProductSelector::SODA, 10)),
        );

        $this->expectException(ProductStockLimitExceededException::class);

        $vendingMachine->changeProductStock(ProductSelector::SODA, 1);
    }

    public function testReturnsChangeAndClearsInsertedMoney(): void
    {
        $vendingMachine = VendingMachine::reconstitute(
            VendingMachineId::random(),
            Money::fromCoins(Coin::ONE_EURO),
            Money::fromCoins(Coin::ONE_EURO, Coin::TWENTY_FIVE_CENTS, Coin::TEN_CENTS),
            ProductInventory::empty(),
        );

        $vendingMachine->returnChangeAndClearInsertedMoney(Money::fromCoins(
            Coin::TWENTY_FIVE_CENTS,
            Coin::TEN_CENTS,
        ));

        self::assertSame(0, $vendingMachine->insertedMoney()->totalCents());
        self::assertSame(100, $vendingMachine->availableChange()->totalCents());
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
