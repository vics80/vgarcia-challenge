<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Support\ValueObject;

use App\VgarciaChallenge\Shared\Domain\ValueObject\FloatValueObject;

final class TestLimitedFloatValueObject extends FloatValueObject
{
    public const ?float MAX = 10.5;
    public const ?float MIN = 1.5;
}
