<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Application\Query;

interface QueryBus
{
    public function ask(Query $query): mixed;
}
