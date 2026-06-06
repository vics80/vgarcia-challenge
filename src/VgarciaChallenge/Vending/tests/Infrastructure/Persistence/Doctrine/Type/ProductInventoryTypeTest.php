<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Product\Product;
use App\VgarciaChallenge\Vending\Domain\Product\ProductId;
use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use App\VgarciaChallenge\Vending\Domain\Product\ProductName;
use App\VgarciaChallenge\Vending\Domain\Product\ProductPrice;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\Product\ProductStockQuantity;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type\ProductInventoryType;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use PHPUnit\Framework\TestCase;

final class ProductInventoryTypeTest extends TestCase
{
    private const string PRODUCT_ID = '018f47d2-7c6a-7caa-b9d4-8b22f1c6d101';

    public function testReturnsJsonSqlDeclaration(): void
    {
        self::assertNotSame('', (new ProductInventoryType())->getSQLDeclaration([], new SQLitePlatform()));
    }

    public function testConvertsValuesToDatabaseValue(): void
    {
        $type = new ProductInventoryType();
        $platform = new SQLitePlatform();
        $inventory = ProductInventory::fromProducts($this->product());
        $payload = [$this->productPayload()];

        self::assertNull($type->convertToDatabaseValue(null, $platform));
        self::assertSame(json_encode($payload, JSON_THROW_ON_ERROR), $type->convertToDatabaseValue($inventory, $platform));
        self::assertSame(json_encode($payload, JSON_THROW_ON_ERROR), $type->convertToDatabaseValue($payload, $platform));
    }

    public function testConvertsValuesToPHPValue(): void
    {
        $type = new ProductInventoryType();
        $platform = new SQLitePlatform();
        $inventory = ProductInventory::fromProducts($this->product());
        $payload = [$this->productPayload()];

        self::assertNull($type->convertToPHPValue(null, $platform));
        self::assertSame($inventory, $type->convertToPHPValue($inventory, $platform));
        self::assertSame($payload, $type->convertToPHPValue(json_encode($payload, JSON_THROW_ON_ERROR), $platform)?->toPrimitives());
        self::assertSame($payload, $type->convertToPHPValue($payload, $platform)?->toPrimitives());
    }

    private function product(): Product
    {
        return Product::create(
            new ProductId(self::PRODUCT_ID),
            new ProductName('Water'),
            ProductSelector::WATER,
            ProductPrice::fromCents(65),
            new ProductStockQuantity(10),
        );
    }

    /** @return array{productId:string,name:string,selector:string,priceCents:int,stockQuantity:int} */
    private function productPayload(): array
    {
        return [
            'productId' => self::PRODUCT_ID,
            'name' => 'Water',
            'selector' => 'WATER',
            'priceCents' => 65,
            'stockQuantity' => 10,
        ];
    }
}
