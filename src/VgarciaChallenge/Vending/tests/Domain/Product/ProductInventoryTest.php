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
    public function testCreatesEmptyInventory(): void
    {
        $inventory = ProductInventory::empty();

        self::assertFalse($inventory->has(ProductSelector::WATER));
        self::assertSame([], $inventory->products());
        self::assertSame([], $inventory->toPrimitives());
    }

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

    public function testCreatesFromPrimitivesAndExportsToPrimitives(): void
    {
        $payload = [
            [
                'productId' => '018f47d2-7c6a-7caa-b9d4-8b22f1c6d101',
                'name' => 'Water',
                'selector' => 'WATER',
                'priceCents' => 65,
                'stockQuantity' => 10,
            ],
            [
                'productId' => '018f47d2-7c6a-7caa-b9d4-8b22f1c6d102',
                'name' => 'Juice',
                'selector' => 'JUICE',
                'priceCents' => 100,
                'stockQuantity' => 9,
            ],
        ];

        $inventory = ProductInventory::fromPrimitives($payload);

        self::assertCount(2, $inventory->products());
        self::assertSame($payload, $inventory->toPrimitives());
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
