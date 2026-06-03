<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Domain\ValueObject;

use JsonSerializable;
use Stringable;

interface ValueObject extends Stringable
{
    public static function create(mixed $value): ?static;

    public function value(): mixed;

    public function equals(?self $valueObject): bool;
}
