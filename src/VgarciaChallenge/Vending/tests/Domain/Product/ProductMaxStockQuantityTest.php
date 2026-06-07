<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\Product;

use App\VgarciaChallenge\Shared\Domain\Specification\Exception\NumberMinMaxException;
use App\VgarciaChallenge\Vending\Domain\Product\ProductMaxStockQuantity;
use PHPUnit\Framework\TestCase;

final class ProductMaxStockQuantityTest extends TestCase
{
    public function testFailsWhenMaxStockQuantityIsLowerThanOne(): void
    {
        $this->expectException(NumberMinMaxException::class);

        new ProductMaxStockQuantity(0);
    }

    public function testAcceptsPositiveMaxStockQuantity(): void
    {
        $maxStockQuantity = new ProductMaxStockQuantity(10);

        self::assertSame(10, $maxStockQuantity->value());
    }
}
