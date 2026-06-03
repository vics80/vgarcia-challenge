<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Support\ValueObject;

use Stringable;

final class TestStringableValue implements Stringable
{
    public function __construct(private readonly string $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
