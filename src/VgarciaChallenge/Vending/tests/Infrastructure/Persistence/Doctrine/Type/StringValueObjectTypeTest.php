<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Product\ProductName;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Exception\DoctrineTypeConversionException;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type\ProductNameType;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use PHPUnit\Framework\TestCase;

final class StringValueObjectTypeTest extends TestCase
{
    public function testReturnsStringSqlDeclaration(): void
    {
        self::assertNotSame('', (new ProductNameType())->getSQLDeclaration([], new SQLitePlatform()));
    }

    public function testConvertsValuesToDatabaseValue(): void
    {
        $type = new ProductNameType();
        $platform = new SQLitePlatform();

        self::assertNull($type->convertToDatabaseValue(null, $platform));
        self::assertSame('Water', $type->convertToDatabaseValue(new ProductName('Water'), $platform));
        self::assertSame('Juice', $type->convertToDatabaseValue('Juice', $platform));
    }

    public function testFailsWhenDatabaseValueIsNotStringCompatible(): void
    {
        $this->expectException(DoctrineTypeConversionException::class);
        $this->expectExceptionMessage('Expected a string value object or string, got int.');

        (new ProductNameType())->convertToDatabaseValue(1, new SQLitePlatform());
    }

    public function testConvertsValuesToPHPValue(): void
    {
        $type = new ProductNameType();
        $platform = new SQLitePlatform();
        $name = new ProductName('Water');

        self::assertNull($type->convertToPHPValue(null, $platform));
        self::assertSame($name, $type->convertToPHPValue($name, $platform));
        self::assertSame('Juice', $type->convertToPHPValue('Juice', $platform)?->value());
    }

    public function testFailsWhenPHPValueIsNotStringCompatible(): void
    {
        $this->expectException(DoctrineTypeConversionException::class);
        $this->expectExceptionMessage('Expected a string database value, got int.');

        (new ProductNameType())->convertToPHPValue(1, new SQLitePlatform());
    }
}
