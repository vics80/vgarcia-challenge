<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\Money;

use App\VgarciaChallenge\Shared\Domain\Specification\Exception\NumberMinMaxException;
use App\VgarciaChallenge\Vending\Domain\Money\CoinMaxInventoryQuantity;
use PHPUnit\Framework\TestCase;

final class CoinMaxInventoryQuantityTest extends TestCase
{
    public function testFailsWhenMaxQuantityIsLowerThanOne(): void
    {
        $this->expectException(NumberMinMaxException::class);

        new CoinMaxInventoryQuantity(0);
    }

    public function testCreatesMaxQuantity(): void
    {
        $quantity = new CoinMaxInventoryQuantity(10);

        self::assertSame(10, $quantity->value());
    }
}
