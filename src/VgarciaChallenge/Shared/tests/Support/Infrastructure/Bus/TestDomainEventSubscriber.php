<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus;

use App\VgarciaChallenge\Shared\Domain\Event\DomainEvent;
use App\VgarciaChallenge\Shared\Domain\Event\DomainEventSubscriber;

final class TestDomainEventSubscriber implements DomainEventSubscriber
{
    public ?DomainEvent $domainEvent = null;

    public function __invoke(TestDomainEvent $domainEvent): void
    {
        $this->domainEvent = $domainEvent;
    }

    public function subscribedTo(): string
    {
        return TestDomainEvent::class;
    }
}
