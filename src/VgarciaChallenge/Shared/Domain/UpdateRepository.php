<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Domain;

interface UpdateRepository
{
    public function update(AggregateRoot $aggregateRoot): void;
}
