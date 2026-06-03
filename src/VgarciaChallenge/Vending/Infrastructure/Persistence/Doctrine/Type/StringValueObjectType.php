<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Shared\Domain\ValueObject\StringValueObject;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Exception\DoctrineTypeConversionException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class StringValueObjectType extends Type
{
    /** @return class-string<StringValueObject> */
    abstract protected function valueObjectClass(): string;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] ??= 255;

        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof StringValueObject) {
            return $value->value();
        }

        if (is_string($value)) {
            return $value;
        }

        throw DoctrineTypeConversionException::forUnexpectedValue('a string value object or string', $value);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?StringValueObject
    {
        if (null === $value || $value instanceof StringValueObject) {
            return $value;
        }

        if (!is_string($value)) {
            throw DoctrineTypeConversionException::forUnexpectedValue('a string database value', $value);
        }

        $className = $this->valueObjectClass();

        return new $className($value);
    }
}
