<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\Money;

use App\VgarciaChallenge\Shared\Domain\Specification\Exception\NumberMinMaxException;
use App\VgarciaChallenge\Vending\Domain\Money\CoinInventoryQuantity;
use PHPUnit\Framework\TestCase;

final class CoinInventoryQuantityTest extends TestCase
{
    public function testFailsWhenQuantityIsNegative(): void
    {
        $this->expectException(NumberMinMaxException::class);

        new CoinInventoryQuantity(-1);
    }

    public function testCreatesQuantity(): void
    {
        $quantity = new CoinInventoryQuantity(0);

        self::assertSame(0, $quantity->value());
    }
}
