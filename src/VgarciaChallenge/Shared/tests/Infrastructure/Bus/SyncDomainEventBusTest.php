<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Infrastructure\Bus;

use App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus\NotCallableTestDomainEventSubscriber;
use App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus\OtherTestDomainEvent;
use App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus\TestDomainEvent;
use App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus\TestDomainEventSubscriber;
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
