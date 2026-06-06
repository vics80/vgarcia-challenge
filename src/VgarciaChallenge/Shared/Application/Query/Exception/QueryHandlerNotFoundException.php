<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Application\Query\Exception;

use App\VgarciaChallenge\Shared\Application\Query\Query;
use RuntimeException;

use function sprintf;

final class QueryHandlerNotFoundException extends RuntimeException
{
    public static function forQuery(Query $query): self
    {
        return new self(sprintf('No query handler found for [%s].', $query::class));
    }
}
