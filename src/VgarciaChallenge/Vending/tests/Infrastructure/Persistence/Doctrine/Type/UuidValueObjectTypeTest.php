<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Order\OrderId;
use App\VgarciaChallenge\Vending\Domain\Product\ProductId;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineId;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Exception\DoctrineTypeConversionException;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type\OrderIdType;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type\ProductIdType;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type\VendingMachineIdType;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid as RamseyUuid;

final class UuidValueObjectTypeTest extends TestCase
{
    private const string UUID = '018f47d2-7c6a-7caa-b9d4-8b22f1c6d101';

    public function testReturnsBinarySqlDeclaration(): void
    {
        self::assertNotSame('', (new ProductIdType())->getSQLDeclaration([], new SQLitePlatform()));
    }

    public function testConvertsValuesToDatabaseValue(): void
    {
        $type = new ProductIdType();
        $platform = new SQLitePlatform();
        $bytes = RamseyUuid::fromString(self::UUID)->getBytes();

        self::assertNull($type->convertToDatabaseValue(null, $platform));
        self::assertSame($bytes, $type->convertToDatabaseValue(new ProductId(self::UUID), $platform));
        self::assertSame($bytes, $type->convertToDatabaseValue(self::UUID, $platform));
        self::assertSame($bytes, $type->convertToDatabaseValue($bytes, $platform));
    }

    public function testFailsWhenDatabaseValueIsNotUuidCompatible(): void
    {
        $this->expectException(DoctrineTypeConversionException::class);
        $this->expectExceptionMessage('Expected a UUID value object or UUID string, got int.');

        (new ProductIdType())->convertToDatabaseValue(1, new SQLitePlatform());
    }

    public function testConvertsValuesToPHPValue(): void
    {
        $type = new ProductIdType();
        $platform = new SQLitePlatform();
        $productId = new ProductId(self::UUID);
        $bytes = RamseyUuid::fromString(self::UUID)->getBytes();

        self::assertNull($type->convertToPHPValue(null, $platform));
        self::assertSame($productId, $type->convertToPHPValue($productId, $platform));
        self::assertSame(self::UUID, $type->convertToPHPValue($bytes, $platform)?->value());
        self::assertSame(self::UUID, $type->convertToPHPValue(self::UUID, $platform)?->value());
    }

    public function testFailsWhenPHPValueIsNotUuidCompatible(): void
    {
        $this->expectException(DoctrineTypeConversionException::class);
        $this->expectExceptionMessage('Expected binary UUID bytes or UUID string from the database, got int.');

        (new ProductIdType())->convertToPHPValue(1, new SQLitePlatform());
    }

    #[DataProvider('uuidTypesProvider')]
    public function testConcreteTypesCreateTheirValueObject(
        object $type,
        string $expectedClass,
    ): void {
        self::assertInstanceOf($expectedClass, $type->convertToPHPValue(self::UUID, new SQLitePlatform()));
    }

    public function testUsesBinaryBindingType(): void
    {
        self::assertSame(ParameterType::BINARY, (new ProductIdType())->getBindingType());
    }

    public static function uuidTypesProvider(): iterable
    {
        yield 'order id' => [new OrderIdType(), OrderId::class];
        yield 'product id' => [new ProductIdType(), ProductId::class];
        yield 'vending machine id' => [new VendingMachineIdType(), VendingMachineId::class];
    }
}
