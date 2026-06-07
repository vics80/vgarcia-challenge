<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Infrastructure\Bus;

use App\VgarciaChallenge\Shared\Domain\Event\DomainEvent;
use App\VgarciaChallenge\Shared\Domain\Event\DomainEventBus;
use App\VgarciaChallenge\Shared\Domain\Event\DomainEventSubscriber;

use function is_callable;

final class SyncDomainEventBus implements DomainEventBus
{
    /** @var array<class-string<DomainEvent>, list<callable>> */
    private array $subscribers = [];

    /** @param iterable<DomainEventSubscriber> $subscribers */
    public function __construct(iterable $subscribers)
    {
        foreach ($subscribers as $subscriber) {
            $this->registerSubscriber($subscriber);
        }
    }

    public function publish(DomainEvent ...$domainEvents): void
    {
        foreach ($domainEvents as $domainEvent) {
            foreach ($this->subscribers[$domainEvent::class] ?? [] as $subscriber) {
                $subscriber($domainEvent);
            }
        }
    }

    private function registerSubscriber(DomainEventSubscriber $subscriber): void
    {
        if (!is_callable($subscriber)) {
            return;
        }

        $this->subscribers[$subscriber->subscribedTo()][] = $subscriber;
    }
}
