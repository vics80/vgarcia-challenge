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
class FloatValueObject implements ValueObject
{
    use ValueObjectTrait;

    public const ?float MAX = null;

    public const ?float MIN = null;

    protected readonly float $value;

    public function __construct(float $value)
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
    public function value(): float
    {
        return $this->value;
    }
}
