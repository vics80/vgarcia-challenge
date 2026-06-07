<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\Product;

use App\VgarciaChallenge\Vending\Domain\Product\Exception\InvalidProductStockAdjustmentException;
use App\VgarciaChallenge\Vending\Domain\Product\Exception\ProductStockLimitExceededException;
use App\VgarciaChallenge\Vending\Domain\Product\Exception\ProductStockNotEnoughException;
use App\VgarciaChallenge\Vending\Domain\Product\Product;
use App\VgarciaChallenge\Vending\Domain\Product\ProductId;
use App\VgarciaChallenge\Vending\Domain\Product\ProductMaxStockQuantity;
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
        $maxStockQuantity = new ProductMaxStockQuantity(20);

        $product = Product::create($productId, $name, $selector, $price, $stockQuantity, $maxStockQuantity);

        self::assertSame($productId, $product->productId());
        self::assertSame($name, $product->name());
        self::assertSame($selector, $product->selector());
        self::assertSame($price, $product->price());
        self::assertSame($stockQuantity, $product->stockQuantity());
        self::assertSame($maxStockQuantity, $product->maxStockQuantity());
    }

    public function testReconstitutesProduct(): void
    {
        $product = Product::reconstitute(
            new ProductId(self::PRODUCT_ID),
            new ProductName('Juice'),
            ProductSelector::JUICE,
            ProductPrice::fromCents(100),
            new ProductStockQuantity(8),
            new ProductMaxStockQuantity(15),
        );

        self::assertSame('Juice', $product->name()->value());
        self::assertSame(ProductSelector::JUICE, $product->selector());
        self::assertSame(100, $product->price()->cents());
        self::assertSame(8, $product->stockQuantity()->value());
        self::assertSame(15, $product->maxStockQuantity()->value());
    }

    public function testCreatesFromPrimitivesAndExportsToPrimitives(): void
    {
        $payload = [
            'productId' => self::PRODUCT_ID,
            'name' => 'Soda',
            'selector' => 'SODA',
            'priceCents' => 150,
            'stockQuantity' => 7,
            'maxStockQuantity' => 10,
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
            'maxStockQuantity' => 20,
        ]);

        $product->decrementStock();

        self::assertSame(1, $product->stockQuantity()->value());
    }

    public function testUsesDefaultMaxStockQuantityWhenItIsNotProvided(): void
    {
        $product = Product::fromPrimitives([
            'productId' => self::PRODUCT_ID,
            'name' => 'Juice',
            'selector' => 'JUICE',
            'priceCents' => 100,
            'stockQuantity' => 7,
        ]);

        self::assertSame(15, $product->maxStockQuantity()->value());
    }

    public function testChangesStockByPositiveAndNegativeQuantity(): void
    {
        $product = Product::fromPrimitives([
            'productId' => self::PRODUCT_ID,
            'name' => 'Water',
            'selector' => 'WATER',
            'priceCents' => 65,
            'stockQuantity' => 10,
            'maxStockQuantity' => 20,
        ]);

        $product->changeStockBy(5);
        $product->changeStockBy(-3);

        self::assertSame(12, $product->stockQuantity()->value());
    }

    public function testFailsWhenStockAdjustmentIsZero(): void
    {
        $product = Product::fromPrimitives([
            'productId' => self::PRODUCT_ID,
            'name' => 'Water',
            'selector' => 'WATER',
            'priceCents' => 65,
            'stockQuantity' => 10,
            'maxStockQuantity' => 20,
        ]);

        $this->expectException(InvalidProductStockAdjustmentException::class);

        $product->changeStockBy(0);
    }

    public function testFailsWhenRemovingMoreStockThanAvailable(): void
    {
        $product = Product::fromPrimitives([
            'productId' => self::PRODUCT_ID,
            'name' => 'Water',
            'selector' => 'WATER',
            'priceCents' => 65,
            'stockQuantity' => 2,
            'maxStockQuantity' => 20,
        ]);

        $this->expectException(ProductStockNotEnoughException::class);
        $this->expectExceptionMessage('Product [WATER] has [2] units, cannot remove [3].');

        $product->changeStockBy(-3);
    }

    public function testFailsWhenAddingStockExceedsMaxStock(): void
    {
        $product = Product::fromPrimitives([
            'productId' => self::PRODUCT_ID,
            'name' => 'Water',
            'selector' => 'WATER',
            'priceCents' => 65,
            'stockQuantity' => 18,
            'maxStockQuantity' => 20,
        ]);

        $this->expectException(ProductStockLimitExceededException::class);
        $this->expectExceptionMessage('Product [WATER] cannot have [21] units because the maximum stock is [20].');

        $product->changeStockBy(3);
    }

    public function testFailsWhenReconstitutedStockExceedsMaxStock(): void
    {
        $this->expectException(ProductStockLimitExceededException::class);

        Product::reconstitute(
            new ProductId(self::PRODUCT_ID),
            new ProductName('Soda'),
            ProductSelector::SODA,
            ProductPrice::fromCents(150),
            new ProductStockQuantity(11),
            new ProductMaxStockQuantity(10),
        );
    }
}
