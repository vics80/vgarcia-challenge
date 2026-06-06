<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Domain;

use App\VgarciaChallenge\Shared\Domain\Timestampable;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

final class TimestampableTest extends TestCase
{
    public function testStartsWithoutTimestamps(): void
    {
        $entity = new TestTimestampableEntity();

        self::assertNull($entity->createdAt());
        self::assertNull($entity->updatedAt());
    }

    public function testInitializesMissingTimestamps(): void
    {
        $entity = new TestTimestampableEntity();

        $entity->initialize();

        self::assertInstanceOf(DateTimeInterface::class, $entity->createdAt());
        self::assertSame($entity->createdAt(), $entity->updatedAt());
    }

    public function testInitializesWithExplicitTimestamps(): void
    {
        $createdAt = new DateTimeImmutable('2026-01-01 10:00:00');
        $updatedAt = new DateTimeImmutable('2026-01-02 10:00:00');
        $entity = new TestTimestampableEntity();

        $entity->initialize($createdAt, $updatedAt);

        self::assertSame($createdAt, $entity->createdAt());
        self::assertSame($updatedAt, $entity->updatedAt());
    }

    public function testTouchesWithExplicitTimestamp(): void
    {
        $updatedAt = new DateTimeImmutable('2026-01-02 10:00:00');
        $entity = new TestTimestampableEntity();
        $entity->initialize(new DateTimeImmutable('2026-01-01 10:00:00'));

        $entity->touchNow($updatedAt);

        self::assertSame($updatedAt, $entity->updatedAt());
    }

    public function testTouchesWithCurrentTimestamp(): void
    {
        $entity = new TestTimestampableEntity();
        $entity->initialize(new DateTimeImmutable('2026-01-01 10:00:00'));

        $entity->touchNow();

        self::assertInstanceOf(DateTimeInterface::class, $entity->updatedAt());
    }
}

final class TestTimestampableEntity
{
    use Timestampable;

    public function initialize(
        ?DateTimeInterface $createdAt = null,
        ?DateTimeInterface $updatedAt = null,
    ): void {
        $this->initializeTimestamps($createdAt, $updatedAt);
    }

    public function touchNow(?DateTimeInterface $updatedAt = null): void
    {
        $this->touch($updatedAt);
    }
}
