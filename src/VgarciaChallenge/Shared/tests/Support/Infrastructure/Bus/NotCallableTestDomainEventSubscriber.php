<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus;

use App\VgarciaChallenge\Shared\Domain\Event\DomainEventSubscriber;

final class NotCallableTestDomainEventSubscriber implements DomainEventSubscriber
{
    public function subscribedTo(): string
    {
        return TestDomainEvent::class;
    }
}
