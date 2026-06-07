<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Support\Domain;

use App\Tests\VgarciaChallenge\Shared\Support\ValueObject\TestLimitedStringValueObject;
use App\Tests\VgarciaChallenge\Shared\Support\ValueObject\TestUuidValueObject;
use App\VgarciaChallenge\Shared\Domain\AggregateRoot;
use App\VgarciaChallenge\Shared\Domain\Event\DomainEvent;

final class TestAggregateRoot extends AggregateRoot
{
    protected const array UPDATABLE_KEYS = ['name', 'alias'];

    protected const array REQUIRED_KEYS = ['id', 'name'];

    protected ?TestLimitedStringValueObject $alias = null;

    public function __construct(
        protected TestUuidValueObject $id,
        protected TestLimitedStringValueObject $name,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromPayload(array $data): self
    {
        self::assertAllKeysExists($data);

        return new self($data['id'], $data['name']);
    }

    public function id(): TestUuidValueObject
    {
        return $this->id;
    }

    public function name(): TestLimitedStringValueObject
    {
        return $this->name;
    }

    public function alias(): ?TestLimitedStringValueObject
    {
        return $this->alias;
    }

    public function record(DomainEvent $domainEvent): void
    {
        $this->recordDomainEvent($domainEvent);
    }
}
