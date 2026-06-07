<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Support\Domain;

use App\Tests\VgarciaChallenge\Shared\Support\ValueObject\TestUuidValueObject;
use App\VgarciaChallenge\Shared\Domain\AggregateRoot;

final class OtherTestAggregateRoot extends AggregateRoot
{
    public function __construct(
        protected TestUuidValueObject $id,
    ) {
    }

    public function id(): TestUuidValueObject
    {
        return $this->id;
    }
}
