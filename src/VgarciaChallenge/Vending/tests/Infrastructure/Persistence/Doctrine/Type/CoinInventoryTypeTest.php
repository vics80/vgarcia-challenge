<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\CoinInventory;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type\CoinInventoryType;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use PHPUnit\Framework\TestCase;

final class CoinInventoryTypeTest extends TestCase
{
    public function testReturnsJsonSqlDeclaration(): void
    {
        self::assertNotSame('', (new CoinInventoryType())->getSQLDeclaration([], new SQLitePlatform()));
    }

    public function testConvertsValuesToDatabaseValue(): void
    {
        $type = new CoinInventoryType();
        $platform = new SQLitePlatform();
        $inventory = CoinInventory::fromCoinQuantitiesWithMax(
            [Coin::FIVE_CENTS->cents() => 2],
            [Coin::FIVE_CENTS->cents() => 5],
        );

        self::assertNull($type->convertToDatabaseValue(null, $platform));
        self::assertSame(
            '[{"coinCents":5,"quantity":2,"maxQuantity":5}]',
            $type->convertToDatabaseValue($inventory, $platform),
        );
        self::assertSame(
            '[{"coinCents":25,"quantity":2,"maxQuantity":10}]',
            $type->convertToDatabaseValue([
                ['coinCents' => 25, 'quantity' => 2, 'maxQuantity' => 10],
            ], $platform),
        );
    }

    public function testConvertsValuesToPHPValue(): void
    {
        $type = new CoinInventoryType();
        $platform = new SQLitePlatform();
        $inventory = CoinInventory::fromCoinQuantities([Coin::FIVE_CENTS->cents() => 1]);

        self::assertNull($type->convertToPHPValue(null, $platform));
        self::assertSame($inventory, $type->convertToPHPValue($inventory, $platform));
        self::assertSame(
            25,
            $type->convertToPHPValue('[{"coinCents":25,"quantity":1,"maxQuantity":2}]', $platform)?->totalCents(),
        );
        self::assertSame(
            100,
            $type->convertToPHPValue([['coinCents' => 100, 'quantity' => 1]], $platform)?->maxQuantityOf(Coin::ONE_EURO)->value(),
        );
    }
}
