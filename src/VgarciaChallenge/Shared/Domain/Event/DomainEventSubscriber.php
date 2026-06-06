<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Domain\Event;

interface DomainEventSubscriber
{
    /** @return class-string<DomainEvent> */
    public function subscribedTo(): string;
}
