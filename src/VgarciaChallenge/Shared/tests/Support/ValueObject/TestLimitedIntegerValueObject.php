<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Support\ValueObject;

use App\VgarciaChallenge\Shared\Domain\ValueObject\IntegerValueObject;

final class TestLimitedIntegerValueObject extends IntegerValueObject
{
    public const ?int MAX = 10;
    public const ?int MIN = 1;
}
