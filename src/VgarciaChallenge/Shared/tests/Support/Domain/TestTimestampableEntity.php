<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Support\Domain;

use App\VgarciaChallenge\Shared\Domain\Timestampable;
use DateTimeInterface;

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
