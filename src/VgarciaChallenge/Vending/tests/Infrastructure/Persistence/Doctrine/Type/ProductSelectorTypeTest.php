<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Exception\DoctrineTypeConversionException;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type\ProductSelectorType;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use PHPUnit\Framework\TestCase;

final class ProductSelectorTypeTest extends TestCase
{
    public function testReturnsStringSqlDeclaration(): void
    {
        self::assertNotSame('', (new ProductSelectorType())->getSQLDeclaration([], new SQLitePlatform()));
    }

    public function testConvertsValuesToDatabaseValue(): void
    {
        $type = new ProductSelectorType();
        $platform = new SQLitePlatform();

        self::assertNull($type->convertToDatabaseValue(null, $platform));
        self::assertSame('WATER', $type->convertToDatabaseValue(ProductSelector::WATER, $platform));
        self::assertSame('JUICE', $type->convertToDatabaseValue('JUICE', $platform));
    }

    public function testFailsWhenDatabaseValueIsNotSelectorCompatible(): void
    {
        $this->expectException(DoctrineTypeConversionException::class);
        $this->expectExceptionMessage('Expected a product selector enum or string, got int.');

        (new ProductSelectorType())->convertToDatabaseValue(1, new SQLitePlatform());
    }

    public function testConvertsValuesToPHPValue(): void
    {
        $type = new ProductSelectorType();
        $platform = new SQLitePlatform();

        self::assertNull($type->convertToPHPValue(null, $platform));
        self::assertSame(ProductSelector::WATER, $type->convertToPHPValue(ProductSelector::WATER, $platform));
        self::assertSame(ProductSelector::JUICE, $type->convertToPHPValue('JUICE', $platform));
    }

    public function testFailsWhenPHPValueIsNotSelectorCompatible(): void
    {
        $this->expectException(DoctrineTypeConversionException::class);
        $this->expectExceptionMessage('Expected a product selector string from the database, got int.');

        (new ProductSelectorType())->convertToPHPValue(1, new SQLitePlatform());
    }
}
