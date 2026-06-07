<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Fixture;

use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\CoinInventory;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\Product\Product;
use App\VgarciaChallenge\Vending\Domain\Product\ProductId;
use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\Product\ProductStockQuantity;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class InitialVendingMachineFixture extends Fixture
{
    private const string VENDING_MACHINE_ID = '018f47d2-7c6a-7caa-b9d4-8b22f1c6d000';

    private const int INITIAL_PRODUCT_STOCK = 10;

    private const int INITIAL_CHANGE_COINS_PER_DENOMINATION = 10;

    private const int MAX_CHANGE_COINS_PER_DENOMINATION = 100;

    public function load(ObjectManager $manager): void
    {
        $products = [
            $this->product(ProductSelector::WATER, '018f47d2-7c6a-7caa-b9d4-8b22f1c6d101'),
            $this->product(ProductSelector::JUICE, '018f47d2-7c6a-7caa-b9d4-8b22f1c6d102'),
            $this->product(ProductSelector::SODA, '018f47d2-7c6a-7caa-b9d4-8b22f1c6d103'),
        ];

        foreach ($products as $product) {
            $manager->persist($product);
        }

        $manager->persist(VendingMachine::create(
            new VendingMachineId(self::VENDING_MACHINE_ID),
            $this->initialAvailableChange(),
            ProductInventory::fromProducts(...$products),
        ));

        $manager->flush();
    }

    private function product(ProductSelector $selector, string $productId): Product
    {
        return Product::create(
            new ProductId($productId),
            $selector->defaultName(),
            $selector,
            $selector->defaultPrice(),
            new ProductStockQuantity(self::INITIAL_PRODUCT_STOCK),
        );
    }

    private function initialAvailableChange(): CoinInventory
    {
        return CoinInventory::fromCoinQuantitiesWithMax(
            [
                Coin::FIVE_CENTS->cents() => self::INITIAL_CHANGE_COINS_PER_DENOMINATION,
                Coin::TEN_CENTS->cents() => self::INITIAL_CHANGE_COINS_PER_DENOMINATION,
                Coin::TWENTY_FIVE_CENTS->cents() => self::INITIAL_CHANGE_COINS_PER_DENOMINATION,
                Coin::ONE_EURO->cents() => self::INITIAL_CHANGE_COINS_PER_DENOMINATION,
            ],
            [
                Coin::FIVE_CENTS->cents() => self::MAX_CHANGE_COINS_PER_DENOMINATION,
                Coin::TEN_CENTS->cents() => self::MAX_CHANGE_COINS_PER_DENOMINATION,
                Coin::TWENTY_FIVE_CENTS->cents() => self::MAX_CHANGE_COINS_PER_DENOMINATION,
                Coin::ONE_EURO->cents() => self::MAX_CHANGE_COINS_PER_DENOMINATION,
            ],
        );
    }
}
