<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Domain\Event;

use App\VgarciaChallenge\Shared\Domain\Event\AbstractDomainEvent;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class AbstractDomainEventTest extends TestCase
{
    public function testCreatesEventWithExplicitMetadata(): void
    {
        $occurredOn = new DateTimeImmutable('2026-01-01 10:00:00');

        $event = new TestDomainEvent('aggregate-id', 'event-id', $occurredOn);

        self::assertSame('event-id', $event->eventId());
        self::assertSame('aggregate-id', $event->aggregateId());
        self::assertSame(TestDomainEvent::class, $event->eventName());
        self::assertSame($occurredOn, $event->occurredOn());
    }

    public function testCreatesEventWithGeneratedMetadata(): void
    {
        $event = new TestDomainEvent('aggregate-id');

        self::assertTrue(Uuid::isValid($event->eventId()));
        self::assertSame('aggregate-id', $event->aggregateId());
        self::assertInstanceOf(DateTimeImmutable::class, $event->occurredOn());
    }
}

final class TestDomainEvent extends AbstractDomainEvent
{
}
