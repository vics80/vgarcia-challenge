<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Exception;

use RuntimeException;

use function get_debug_type;
use function sprintf;

final class DoctrineTypeConversionException extends RuntimeException
{
    public static function forUnexpectedValue(string $expected, mixed $value): self
    {
        return new self(sprintf('Expected %s, got %s.', $expected, get_debug_type($value)));
    }
}
