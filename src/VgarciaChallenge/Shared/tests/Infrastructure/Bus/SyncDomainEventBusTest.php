<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Infrastructure\Bus;

use App\VgarciaChallenge\Shared\Domain\Event\AbstractDomainEvent;
use App\VgarciaChallenge\Shared\Domain\Event\DomainEvent;
use App\VgarciaChallenge\Shared\Domain\Event\DomainEventSubscriber;
use App\VgarciaChallenge\Shared\Infrastructure\Bus\SyncDomainEventBus;
use PHPUnit\Framework\TestCase;

final class SyncDomainEventBusTest extends TestCase
{
    public function testPublishesEventToSubscribedCallables(): void
    {
        $subscriber = new TestDomainEventSubscriber();
        $eventBus = new SyncDomainEventBus([$subscriber]);
        $event = new TestDomainEvent('aggregate-id');

        $eventBus->publish($event);

        self::assertSame($event, $subscriber->domainEvent);
    }

    public function testIgnoresEventsWithoutSubscribers(): void
    {
        $subscriber = new TestDomainEventSubscriber();
        $eventBus = new SyncDomainEventBus([$subscriber]);

        $eventBus->publish(new OtherTestDomainEvent('aggregate-id'));

        self::assertNull($subscriber->domainEvent);
    }

    public function testIgnoresSubscribersThatAreNotCallable(): void
    {
        $eventBus = new SyncDomainEventBus([new NotCallableTestDomainEventSubscriber()]);

        $eventBus->publish(new TestDomainEvent('aggregate-id'));

        self::assertTrue(true);
    }
}

final class TestDomainEvent extends AbstractDomainEvent
{
}

final class OtherTestDomainEvent extends AbstractDomainEvent
{
}

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

final class NotCallableTestDomainEventSubscriber implements DomainEventSubscriber
{
    public function subscribedTo(): string
    {
        return TestDomainEvent::class;
    }
}
