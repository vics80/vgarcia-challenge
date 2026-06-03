<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use JsonException;

use function json_decode;
use function json_encode;

final class ProductInventoryType extends Type
{
    public const string NAME = 'product_inventory';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    /** @throws JsonException */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof ProductInventory) {
            return json_encode($value->toPrimitives(), JSON_THROW_ON_ERROR);
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    /** @throws JsonException */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?ProductInventory
    {
        if (null === $value || $value instanceof ProductInventory) {
            return $value;
        }

        $payload = is_string($value) ? json_decode($value, true, flags: JSON_THROW_ON_ERROR) : $value;

        return ProductInventory::fromPrimitives($payload);
    }
}
