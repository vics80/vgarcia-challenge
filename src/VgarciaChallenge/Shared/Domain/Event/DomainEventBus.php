<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Domain\Event;

interface DomainEventBus
{
    public function publish(DomainEvent ...$domainEvents): void;
}
