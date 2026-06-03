<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Domain\ValueObject;

use Override;

trait ValueObjectTrait
{
    public static function create(mixed $value): ?static
    {
        return null !== $value ? new static($value) : null;
    }

    #[Override]
    public function __toString(): string
    {
        return (string) $this->value;
    }

    #[Override]
    public function equals(?ValueObject $valueObject): bool
    {
        $class = static::class;

        return ($valueObject instanceof $class) && $this->value() === $valueObject->value();
    }

    protected function isValid(): void
    {
    }
}
