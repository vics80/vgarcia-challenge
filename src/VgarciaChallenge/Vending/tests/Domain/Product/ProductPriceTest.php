<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\Product;

use App\VgarciaChallenge\Shared\Domain\Specification\Exception\NumberMinMaxException;
use App\VgarciaChallenge\Vending\Domain\Product\ProductPrice;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProductPriceTest extends TestCase
{
    #[DataProvider('invalidPricesProvider')]
    public function testFailsWhenPriceIsNotPositive(int $priceCents): void
    {
        $this->expectException(NumberMinMaxException::class);

        ProductPrice::fromCents($priceCents);
    }

    public function testAcceptsPositivePriceInCents(): void
    {
        $price = ProductPrice::fromCents(65);

        self::assertSame(65, $price->cents());
        self::assertSame('0.65', $price->decimalString());
    }

    public static function invalidPricesProvider(): iterable
    {
        yield 'zero price' => [0];
        yield 'negative price' => [-1];
    }
}
