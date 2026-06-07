<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Domain;

use DateTimeImmutable;
use DateTimeInterface;

trait Timestampable
{
    public const string CREATED_AT_KEY = 'createdAt';

    public const string UPDATED_AT_KEY = 'updatedAt';

    protected ?DateTimeInterface $createdAt = null;

    protected ?DateTimeInterface $updatedAt = null;

    public function createdAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    protected function initializeTimestamps(
        ?DateTimeInterface $createdAt = null,
        ?DateTimeInterface $updatedAt = null,
    ): void {
        $now = new DateTimeImmutable();

        $this->createdAt = $createdAt ?? $now;
        $this->updatedAt = $updatedAt ?? $this->createdAt;
    }

    protected function touch(?DateTimeInterface $updatedAt = null): void
    {
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }
}
