<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Order\OrderTotalAmount;
use App\VgarciaChallenge\Vending\Domain\Product\ProductPrice;
use App\VgarciaChallenge\Vending\Domain\Product\ProductStockQuantity;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Exception\DoctrineTypeConversionException;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type\OrderTotalAmountType;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type\ProductPriceType;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type\ProductStockQuantityType;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IntegerValueObjectTypeTest extends TestCase
{
    public function testReturnsIntegerSqlDeclaration(): void
    {
        self::assertNotSame('', (new ProductPriceType())->getSQLDeclaration([], new SQLitePlatform()));
    }

    public function testConvertsValuesToDatabaseValue(): void
    {
        $type = new ProductPriceType();
        $platform = new SQLitePlatform();

        self::assertNull($type->convertToDatabaseValue(null, $platform));
        self::assertSame(65, $type->convertToDatabaseValue(ProductPrice::fromCents(65), $platform));
        self::assertSame(100, $type->convertToDatabaseValue(100, $platform));
    }

    public function testFailsWhenDatabaseValueIsNotIntegerCompatible(): void
    {
        $this->expectException(DoctrineTypeConversionException::class);
        $this->expectExceptionMessage('Expected an integer value object or integer, got array.');

        (new ProductPriceType())->convertToDatabaseValue([], new SQLitePlatform());
    }

    public function testConvertsValuesToPHPValue(): void
    {
        $type = new ProductPriceType();
        $platform = new SQLitePlatform();
        $price = ProductPrice::fromCents(65);

        self::assertNull($type->convertToPHPValue(null, $platform));
        self::assertSame($price, $type->convertToPHPValue($price, $platform));
        self::assertSame(65, $type->convertToPHPValue('65', $platform)?->value());
        self::assertSame(100, $type->convertToPHPValue(100, $platform)?->value());
    }

    public function testFailsWhenPHPValueIsNotIntegerCompatible(): void
    {
        $this->expectException(DoctrineTypeConversionException::class);
        $this->expectExceptionMessage('Expected an integer-compatible database value, got array.');

        (new ProductPriceType())->convertToPHPValue([], new SQLitePlatform());
    }

    #[DataProvider('integerTypesProvider')]
    public function testConcreteTypesCreateTheirValueObject(
        object $type,
        string $expectedClass,
    ): void {
        $valueObject = $type->convertToPHPValue(10, new SQLitePlatform());

        self::assertInstanceOf($expectedClass, $valueObject);
        self::assertSame(10, $valueObject->value());
    }

    public static function integerTypesProvider(): iterable
    {
        yield 'order total amount' => [new OrderTotalAmountType(), OrderTotalAmount::class];
        yield 'product price' => [new ProductPriceType(), ProductPrice::class];
        yield 'product stock quantity' => [new ProductStockQuantityType(), ProductStockQuantity::class];
    }
}
