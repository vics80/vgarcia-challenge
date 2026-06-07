<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Domain\ValueObject;

use App\VgarciaChallenge\Shared\Domain\Specification\Exception\MinMaxException;
use Override;
use Stringable;

/**
 * @phpstan-consistent-constructor
 */
class StringValueObject implements ValueObject
{
    use ValueObjectTrait;

    public const ?int MAX_LENGTH = null;

    protected readonly string $value;

    public function __construct(string|Stringable $value)
    {
        $this->value = (string) $value;
        $this->isValid();
    }

    protected function isValid(): void
    {
        if (null !== static::MAX_LENGTH && strlen($this->value()) > static::MAX_LENGTH) {
            throw new MinMaxException();
        }
    }

    #[Override]
    public function value(): string
    {
        return $this->value;
    }
}
