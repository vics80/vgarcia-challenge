<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Support\ValueObject;

use App\VgarciaChallenge\Shared\Domain\ValueObject\StringValueObject;

final class TestLimitedStringValueObject extends StringValueObject
{
    public const ?int MAX_LENGTH = 4;
}
