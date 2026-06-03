<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Domain\Event;

use DateTimeImmutable;

interface DomainEvent
{
    public function eventId(): string;

    public function aggregateId(): string;

    public function eventName(): string;

    public function occurredOn(): DateTimeImmutable;
}
