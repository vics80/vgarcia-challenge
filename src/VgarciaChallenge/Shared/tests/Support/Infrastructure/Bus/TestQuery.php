<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus;

use App\VgarciaChallenge\Shared\Application\Query\Query;

final class TestQuery implements Query
{
    public function __construct(
        public readonly string $criteria,
    ) {
    }
}
