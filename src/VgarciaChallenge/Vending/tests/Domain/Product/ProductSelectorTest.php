<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\Product;

use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProductSelectorTest extends TestCase
{
    #[DataProvider('selectorsProvider')]
    public function testReturnsDefaultNameAndPrice(
        ProductSelector $selector,
        string $expectedName,
        int $expectedPriceCents,
    ): void {
        self::assertSame($expectedName, $selector->defaultName()->value());
        self::assertSame($expectedPriceCents, $selector->defaultPrice()->cents());
    }

    public static function selectorsProvider(): iterable
    {
        yield 'water' => [ProductSelector::WATER, 'Water', 65];
        yield 'juice' => [ProductSelector::JUICE, 'Juice', 100];
        yield 'soda' => [ProductSelector::SODA, 'Soda', 150];
    }
}
