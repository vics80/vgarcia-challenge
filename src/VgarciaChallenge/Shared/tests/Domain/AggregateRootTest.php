<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Domain;

use App\Tests\VgarciaChallenge\Shared\Support\Domain\OtherTestAggregateRoot;
use App\Tests\VgarciaChallenge\Shared\Support\Domain\RecordingUpdateRepository;
use App\Tests\VgarciaChallenge\Shared\Support\Domain\TestAggregateDomainEvent;
use App\Tests\VgarciaChallenge\Shared\Support\Domain\TestAggregateRoot;
use App\Tests\VgarciaChallenge\Shared\Support\ValueObject\TestLimitedStringValueObject;
use App\Tests\VgarciaChallenge\Shared\Support\ValueObject\TestUuidValueObject;
use App\VgarciaChallenge\Shared\Domain\Exception\MissingRequiredKeyException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AggregateRootTest extends TestCase
{
    private const string FIRST_ID = '018f47d2-7c6a-7caa-b9d4-8b22f1c6d001';

    private const string SECOND_ID = '018f47d2-7c6a-7caa-b9d4-8b22f1c6d002';

    public function testCreatesFromPayloadWhenRequiredKeysArePresent(): void
    {
        $aggregateRoot = TestAggregateRoot::fromPayload([
            'id' => new TestUuidValueObject(self::FIRST_ID),
            'name' => new TestLimitedStringValueObject('root'),
        ]);

        self::assertSame(self::FIRST_ID, $aggregateRoot->id()->value());
        self::assertSame('root', $aggregateRoot->name()->value());
    }

    public function testFailsWhenRequiredKeyIsMissing(): void
    {
        $this->expectException(MissingRequiredKeyException::class);
        $this->expectExceptionMessage('Key [name] not set in payload.');

        TestAggregateRoot::fromPayload([
            'id' => new TestUuidValueObject(self::FIRST_ID),
        ]);
    }

    public function testReturnsEntityName(): void
    {
        $aggregateRoot = new TestAggregateRoot(
            new TestUuidValueObject(self::FIRST_ID),
            new TestLimitedStringValueObject('root'),
        );

        self::assertSame('TestAggregateRoot', $aggregateRoot->entityName());
    }

    public function testUpdatesOnlyChangedUpdatableValuesAndPersistsAggregate(): void
    {
        $aggregateRoot = new TestAggregateRoot(
            new TestUuidValueObject(self::FIRST_ID),
            new TestLimitedStringValueObject('old'),
        );
        $repository = new RecordingUpdateRepository();

        $aggregateRoot->update($repository, $this->updatePayload());

        self::assertSame('new', $aggregateRoot->name()->value());
        self::assertSame('mark', $aggregateRoot->alias()?->value());
        self::assertSame($aggregateRoot, $repository->aggregateRoot);
        self::assertSame(1, $repository->updates);
    }

    public function testComparesAggregatesByClassAndIdentifier(): void
    {
        $aggregateRoot = new TestAggregateRoot(
            new TestUuidValueObject(self::FIRST_ID),
            new TestLimitedStringValueObject('root'),
        );
        $sameIdentifier = new TestAggregateRoot(
            new TestUuidValueObject(self::FIRST_ID),
            new TestLimitedStringValueObject('copy'),
        );
        $differentIdentifier = new TestAggregateRoot(
            new TestUuidValueObject(self::SECOND_ID),
            new TestLimitedStringValueObject('root'),
        );
        $differentClass = new OtherTestAggregateRoot(new TestUuidValueObject(self::FIRST_ID));

        self::assertTrue($aggregateRoot->equals($sameIdentifier));
        self::assertFalse($aggregateRoot->equals($differentIdentifier));
        self::assertFalse($aggregateRoot->equals($differentClass));
    }

    public function testRecordsAndPullsDomainEvents(): void
    {
        $aggregateRoot = new TestAggregateRoot(
            new TestUuidValueObject(self::FIRST_ID),
            new TestLimitedStringValueObject('root'),
        );
        $event = new TestAggregateDomainEvent(
            self::FIRST_ID,
            'event-id',
            new DateTimeImmutable('2026-01-01 10:00:00'),
        );

        $aggregateRoot->record($event);

        self::assertSame([$event], $aggregateRoot->pullDomainEvents());
        self::assertSame([], $aggregateRoot->pullDomainEvents());
    }

    public function testStringifiesAsJson(): void
    {
        $aggregateRoot = new TestAggregateRoot(
            new TestUuidValueObject(self::FIRST_ID),
            new TestLimitedStringValueObject('root'),
        );

        self::assertSame('{}', (string) $aggregateRoot);
    }

    /** @return iterable<string, TestLimitedStringValueObject> */
    private function updatePayload(): iterable
    {
        yield 'ignored' => new TestLimitedStringValueObject('nope');
        yield 'name' => new TestLimitedStringValueObject('old');
        yield 'name' => new TestLimitedStringValueObject('new');
        yield 'alias' => new TestLimitedStringValueObject('mark');
    }
}
