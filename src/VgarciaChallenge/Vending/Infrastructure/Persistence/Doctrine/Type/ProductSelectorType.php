<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Exception\DoctrineTypeConversionException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class ProductSelectorType extends Type
{
    public const string NAME = 'product_selector';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] = 20;

        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof ProductSelector) {
            return $value->value;
        }

        if (is_string($value)) {
            return $value;
        }

        throw DoctrineTypeConversionException::forUnexpectedValue('a product selector enum or string', $value);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?ProductSelector
    {
        if (null === $value || $value instanceof ProductSelector) {
            return $value;
        }

        if (!is_string($value)) {
            throw DoctrineTypeConversionException::forUnexpectedValue('a product selector string from the database', $value);
        }

        return ProductSelector::from($value);
    }
}
