<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Domain;

use App\VgarciaChallenge\Shared\Domain\Event\DomainEvent;
use App\VgarciaChallenge\Shared\Domain\Exception\MissingRequiredKeyException;
use App\VgarciaChallenge\Shared\Domain\ValueObject\ValueObject;
use JsonException;
use ReflectionClass;

use function array_key_exists;
use function in_array;
use function json_encode;

abstract class AggregateRoot
{
    protected const array UPDATABLE_KEYS = [];

    protected const array REQUIRED_KEYS = [];

    public const string ID_KEY = 'id';

    /** @var list<DomainEvent> */
    private array $domainEvents = [];

    /** @param array<string,mixed> $data */
    protected static function assertAllKeysExists(array $data): void
    {
        foreach (static::REQUIRED_KEYS as $key) {
            if (!array_key_exists($key, $data)) {
                throw MissingRequiredKeyException::forKey($key);
            }
        }
    }

    public function entityName(): string
    {
        return (new ReflectionClass($this))->getShortName();
    }

    /** @param iterable<string, mixed> $data */
    public function update(UpdateRepository $updateRepository, iterable $data): void
    {
        foreach ($data as $key => $newValue) {
            if (!in_array($key, static::UPDATABLE_KEYS, true)) {
                continue;
            }

            /** @var ValueObject|null $oldValue */
            $oldValue = $this->{$key};
            if ($oldValue?->equals($newValue)) {
                continue;
            }

            $this->{$key} = $newValue;
        }

        $updateRepository->update($this);
    }

    public function equals(self $aggregateRoot): bool
    {
        return $aggregateRoot instanceof $this && $aggregateRoot->getIdentifier()->equals($this->getIdentifier());
    }

    /** @return list<DomainEvent> */
    final public function pullDomainEvents(): array
    {
        $domainEvents = $this->domainEvents;
        $this->domainEvents = [];

        return $domainEvents;
    }

    final protected function recordDomainEvent(DomainEvent $domainEvent): void
    {
        $this->domainEvents[] = $domainEvent;
    }

    /** @throws JsonException */
    public function __toString(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }

    final protected function getIdentifier(): ValueObject
    {
        return $this->{static::ID_KEY}();
    }
}
