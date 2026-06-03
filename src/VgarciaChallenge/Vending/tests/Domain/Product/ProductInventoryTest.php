<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\Product;

use App\VgarciaChallenge\Vending\Domain\Product\Exception\DuplicateProductSelectorException;
use App\VgarciaChallenge\Vending\Domain\Product\Product;
use App\VgarciaChallenge\Vending\Domain\Product\ProductId;
use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use App\VgarciaChallenge\Vending\Domain\Product\ProductName;
use App\VgarciaChallenge\Vending\Domain\Product\ProductPrice;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\Product\ProductStockQuantity;
use PHPUnit\Framework\TestCase;

final class ProductInventoryTest extends TestCase
{
    public function testFailsWhenTwoProductsUseSameSelector(): void
    {
        $this->expectException(DuplicateProductSelectorException::class);

        ProductInventory::fromProducts(
            $this->product(ProductSelector::WATER),
            $this->product(ProductSelector::WATER),
        );
    }

    public function testFindsProductBySelector(): void
    {
        $water = $this->product(ProductSelector::WATER);
        $inventory = ProductInventory::fromProducts($water);

        self::assertTrue($inventory->has(ProductSelector::WATER));
        self::assertSame($water, $inventory->find(ProductSelector::WATER));
        self::assertNull($inventory->find(ProductSelector::SODA));
    }

    private function product(ProductSelector $selector): Product
    {
        return Product::create(
            ProductId::random(),
            new ProductName($selector->defaultName()->value()),
            $selector,
            $selector->defaultPrice(),
            new ProductStockQuantity(10),
        );
    }
}
