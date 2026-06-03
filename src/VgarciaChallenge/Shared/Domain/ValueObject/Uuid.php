<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Domain\ValueObject;

use App\VgarciaChallenge\Shared\Domain\ValueObject\Exception\InvalidUuidException;
use Override;
use Ramsey\Uuid\Uuid as RamseyUuid;

class Uuid extends StringValueObject
{
    final public static function random(): static
    {
        return new static(RamseyUuid::uuid7()->toString());
    }

    protected function isValid(): void
    {
        if (!self::validate($this->value)) {
            throw new InvalidUuidException();
        }
    }

    public static function validate(string $value): bool
    {
        return RamseyUuid::isValid($value);
    }
}
