<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\Product;

use App\VgarciaChallenge\Vending\Domain\Product\Product;
use App\VgarciaChallenge\Vending\Domain\Product\ProductId;
use App\VgarciaChallenge\Vending\Domain\Product\ProductName;
use App\VgarciaChallenge\Vending\Domain\Product\ProductPrice;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\Product\ProductStockQuantity;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    private const string PRODUCT_ID = '018f47d2-7c6a-7caa-b9d4-8b22f1c6d101';

    public function testCreatesProductAndExposesValues(): void
    {
        $productId = new ProductId(self::PRODUCT_ID);
        $name = new ProductName('Water');
        $selector = ProductSelector::WATER;
        $price = ProductPrice::fromCents(65);
        $stockQuantity = new ProductStockQuantity(10);

        $product = Product::create($productId, $name, $selector, $price, $stockQuantity);

        self::assertSame($productId, $product->productId());
        self::assertSame($name, $product->name());
        self::assertSame($selector, $product->selector());
        self::assertSame($price, $product->price());
        self::assertSame($stockQuantity, $product->stockQuantity());
    }

    public function testReconstitutesProduct(): void
    {
        $product = Product::reconstitute(
            new ProductId(self::PRODUCT_ID),
            new ProductName('Juice'),
            ProductSelector::JUICE,
            ProductPrice::fromCents(100),
            new ProductStockQuantity(8),
        );

        self::assertSame('Juice', $product->name()->value());
        self::assertSame(ProductSelector::JUICE, $product->selector());
        self::assertSame(100, $product->price()->cents());
        self::assertSame(8, $product->stockQuantity()->value());
    }

    public function testCreatesFromPrimitivesAndExportsToPrimitives(): void
    {
        $payload = [
            'productId' => self::PRODUCT_ID,
            'name' => 'Soda',
            'selector' => 'SODA',
            'priceCents' => 150,
            'stockQuantity' => 7,
        ];

        $product = Product::fromPrimitives($payload);

        self::assertSame($payload, $product->toPrimitives());
    }

    public function testDecrementsStock(): void
    {
        $product = Product::fromPrimitives([
            'productId' => self::PRODUCT_ID,
            'name' => 'Water',
            'selector' => 'WATER',
            'priceCents' => 65,
            'stockQuantity' => 2,
        ]);

        $product->decrementStock();

        self::assertSame(1, $product->stockQuantity()->value());
    }
}
