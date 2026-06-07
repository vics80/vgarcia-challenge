<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Domain\Event;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

abstract class AbstractDomainEvent implements DomainEvent
{
    private readonly string $eventId;

    private readonly DateTimeImmutable $occurredOn;

    public function __construct(
        private readonly string $aggregateId,
        ?string $eventId = null,
        ?DateTimeImmutable $occurredOn = null,
    ) {
        $this->eventId = $eventId ?? Uuid::uuid7()->toString();
        $this->occurredOn = $occurredOn ?? new DateTimeImmutable();
    }

    public function eventId(): string
    {
        return $this->eventId;
    }

    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    public function eventName(): string
    {
        return static::class;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
