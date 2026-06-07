<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Support\Domain;

use App\VgarciaChallenge\Shared\Domain\AggregateRoot;
use App\VgarciaChallenge\Shared\Domain\UpdateRepository;

final class RecordingUpdateRepository implements UpdateRepository
{
    public ?AggregateRoot $aggregateRoot = null;
    public int $updates = 0;

    public function update(AggregateRoot $aggregateRoot): void
    {
        $this->aggregateRoot = $aggregateRoot;
        ++$this->updates;
    }
}
