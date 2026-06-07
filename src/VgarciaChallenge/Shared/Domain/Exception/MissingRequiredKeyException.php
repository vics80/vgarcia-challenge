<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Domain\Exception;

use function sprintf;

final class MissingRequiredKeyException extends DomainException
{
    public static function forKey(string $key): self
    {
        return new self(sprintf('Key [%s] not set in payload.', $key));
    }
}
