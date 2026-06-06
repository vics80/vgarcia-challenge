<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Domain\ValueObject;

use App\VgarciaChallenge\Shared\Domain\ValueObject\ValueObject;
use App\VgarciaChallenge\Shared\Domain\ValueObject\ValueObjectTrait;
use PHPUnit\Framework\TestCase;

final class ValueObjectTraitTest extends TestCase
{
    public function testCreateReturnsInstanceWhenValueIsNotNull(): void
    {
        $valueObject = TestPlainValueObject::create('value');

        self::assertInstanceOf(TestPlainValueObject::class, $valueObject);
        self::assertSame('value', $valueObject->value());
    }

    public function testEqualsReturnsFalseWhenComparedWithNull(): void
    {
        $valueObject = new TestPlainValueObject('value');

        self::assertFalse($valueObject->equals(null));
    }
}

final class TestPlainValueObject implements ValueObject
{
    use ValueObjectTrait;

    public function __construct(
        private readonly string $value,
    ) {
        $this->isValid();
    }

    public function value(): string
    {
        return $this->value;
    }
}
