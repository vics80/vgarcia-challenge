<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Application\Query;

interface QueryHandler
{
    /** @return class-string<Query> */
    public function handles(): string;
}
