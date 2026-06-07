<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Money\CoinInventory;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use JsonException;

use function json_decode;
use function json_encode;

final class CoinInventoryType extends Type
{
    public const string NAME = 'coin_inventory';

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

        if ($value instanceof CoinInventory) {
            return json_encode($value->toPrimitives(), JSON_THROW_ON_ERROR);
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    /** @throws JsonException */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?CoinInventory
    {
        if (null === $value || $value instanceof CoinInventory) {
            return $value;
        }

        $payload = is_string($value) ? json_decode($value, true, flags: JSON_THROW_ON_ERROR) : $value;

        return CoinInventory::fromPrimitives($payload);
    }
}
