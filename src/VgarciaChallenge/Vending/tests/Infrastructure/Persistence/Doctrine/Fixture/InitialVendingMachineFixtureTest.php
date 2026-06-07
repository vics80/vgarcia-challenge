<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Fixture;

use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Product\Product;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Fixture\InitialVendingMachineFixture;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

final class InitialVendingMachineFixtureTest extends TestCase
{
    public function testLoadsInitialProductsAndVendingMachine(): void
    {
        $persistedObjects = [];
        $manager = $this->createMock(ObjectManager::class);
        $manager
            ->expects(self::exactly(4))
            ->method('persist')
            ->willReturnCallback(static function (object $object) use (&$persistedObjects): void {
                $persistedObjects[] = $object;
            });
        $manager
            ->expects(self::once())
            ->method('flush');

        (new InitialVendingMachineFixture())->load($manager);

        self::assertCount(4, $persistedObjects);
        self::assertContainsOnlyInstancesOf(Product::class, array_slice($persistedObjects, 0, 3));

        $products = array_slice($persistedObjects, 0, 3);
        self::assertSame(ProductSelector::WATER, $products[0]->selector());
        self::assertSame('Water', $products[0]->name()->value());
        self::assertSame(65, $products[0]->price()->cents());
        self::assertSame(10, $products[0]->stockQuantity()->value());
        self::assertSame(20, $products[0]->maxStockQuantity()->value());
        self::assertSame(ProductSelector::JUICE, $products[1]->selector());
        self::assertSame(15, $products[1]->maxStockQuantity()->value());
        self::assertSame(ProductSelector::SODA, $products[2]->selector());
        self::assertSame(10, $products[2]->maxStockQuantity()->value());

        $vendingMachine = $persistedObjects[3];
        self::assertInstanceOf(VendingMachine::class, $vendingMachine);
        self::assertSame('018f47d2-7c6a-7caa-b9d4-8b22f1c6d000', $vendingMachine->vendingMachineId()->value());
        self::assertSame(0, $vendingMachine->insertedMoney()->totalCents());
        self::assertSame(1400, $vendingMachine->availableChange()->totalCents());
        self::assertSame(100, $vendingMachine->availableChange()->maxQuantityOf(Coin::ONE_EURO)->value());
        self::assertCount(3, $vendingMachine->productInventory()->products());
    }
}
