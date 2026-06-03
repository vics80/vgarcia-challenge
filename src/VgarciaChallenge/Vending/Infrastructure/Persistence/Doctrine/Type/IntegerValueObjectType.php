<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Shared\Domain\ValueObject\IntegerValueObject;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Exception\DoctrineTypeConversionException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class IntegerValueObjectType extends Type
{
    /** @return class-string<IntegerValueObject> */
    abstract protected function valueObjectClass(): string;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?int
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof IntegerValueObject) {
            return $value->value();
        }

        if (is_int($value)) {
            return $value;
        }

        throw DoctrineTypeConversionException::forUnexpectedValue('an integer value object or integer', $value);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?IntegerValueObject
    {
        if (null === $value || $value instanceof IntegerValueObject) {
            return $value;
        }

        if (!is_int($value) && !is_numeric($value)) {
            throw DoctrineTypeConversionException::forUnexpectedValue('an integer-compatible database value', $value);
        }

        $className = $this->valueObjectClass();

        return new $className((int) $value);
    }
}
