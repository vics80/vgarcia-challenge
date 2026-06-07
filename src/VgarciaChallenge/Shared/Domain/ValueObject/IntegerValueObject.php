<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Domain\ValueObject;

use App\VgarciaChallenge\Shared\Domain\Specification\Exception\MinMaxException;
use App\VgarciaChallenge\Shared\Domain\Specification\Exception\NumberMinMaxException;
use Override;
use Stringable;

/**
 * @phpstan-consistent-constructor
 */
class IntegerValueObject implements ValueObject
{
    use ValueObjectTrait;

    public const ?int MAX = null;

    public const ?int MIN = null;

    protected readonly int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
        $this->isValid();
    }

    protected function isValid(): void
    {
        if (null !== static::MAX && $this->value() > static::MAX) {
            throw new NumberMinMaxException();
        }

        if (null !== static::MIN && $this->value() < static::MIN) {
            throw new NumberMinMaxException();
        }
    }

    #[Override]
    public function value(): int
    {
        return $this->value;
    }
}
