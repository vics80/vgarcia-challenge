<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Shared\Domain\ValueObject\Uuid;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Exception\DoctrineTypeConversionException;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Ramsey\Uuid\Uuid as RamseyUuid;

use function is_string;
use function strlen;

abstract class UuidValueObjectType extends Type
{
    /** @return class-string<Uuid> */
    abstract protected function valueObjectClass(): string;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] = 16;
        $column['fixed'] = true;

        return $platform->getBinaryTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof Uuid) {
            return RamseyUuid::fromString($value->value())->getBytes();
        }

        if (is_string($value)) {
            return 16 === strlen($value) ? $value : RamseyUuid::fromString($value)->getBytes();
        }

        throw DoctrineTypeConversionException::forUnexpectedValue('a UUID value object or UUID string', $value);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Uuid
    {
        if (null === $value || $value instanceof Uuid) {
            return $value;
        }

        if (!is_string($value)) {
            throw DoctrineTypeConversionException::forUnexpectedValue('binary UUID bytes or UUID string from the database', $value);
        }

        $uuid = 16 === strlen($value) ? RamseyUuid::fromBytes($value)->toString() : $value;
        $className = $this->valueObjectClass();

        return new $className($uuid);
    }

    public function getBindingType(): ParameterType
    {
        return ParameterType::BINARY;
    }
}
