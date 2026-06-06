<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\Product;

use App\VgarciaChallenge\Shared\Domain\Specification\Exception\NumberMinMaxException;
use App\VgarciaChallenge\Vending\Domain\Product\ProductStockQuantity;
use PHPUnit\Framework\TestCase;

final class ProductStockQuantityTest extends TestCase
{
    public function testFailsWhenStockQuantityIsNegative(): void
    {
        $this->expectException(NumberMinMaxException::class);

        new ProductStockQuantity(-1);
    }

    public function testAcceptsZeroStockQuantity(): void
    {
        $stockQuantity = new ProductStockQuantity(0);

        self::assertSame(0, $stockQuantity->value());
    }

    public function testDecrementsStockQuantity(): void
    {
        $stockQuantity = new ProductStockQuantity(3);

        self::assertSame(2, $stockQuantity->decrement()->value());
        self::assertSame(1, $stockQuantity->decrement(2)->value());
    }

    public function testFailsWhenDecrementLeavesNegativeStockQuantity(): void
    {
        $stockQuantity = new ProductStockQuantity(0);

        $this->expectException(NumberMinMaxException::class);

        $stockQuantity->decrement();
    }
}
