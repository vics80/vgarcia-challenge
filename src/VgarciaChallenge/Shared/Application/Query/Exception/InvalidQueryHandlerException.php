<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Application\Query\Exception;

use App\VgarciaChallenge\Shared\Application\Query\QueryHandler;
use RuntimeException;

use function sprintf;

final class InvalidQueryHandlerException extends RuntimeException
{
    public static function forHandler(QueryHandler $handler): self
    {
        return new self(sprintf('Query handler [%s] must be callable.', $handler::class));
    }
}
