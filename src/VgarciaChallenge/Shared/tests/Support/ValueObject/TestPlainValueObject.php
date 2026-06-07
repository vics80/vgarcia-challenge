<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Support\ValueObject;

use App\VgarciaChallenge\Shared\Domain\ValueObject\ValueObject;
use App\VgarciaChallenge\Shared\Domain\ValueObject\ValueObjectTrait;

final class TestPlainValueObject implements ValueObject
{
    use ValueObjectTrait;

    public function __construct(
        private readonly string $value,
    ) {
        $this->isValid();
    }

    public function value(): string
    {
        return $this->value;
    }
}
