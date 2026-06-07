<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Domain\ValueObject;

use App\Tests\VgarciaChallenge\Shared\Support\ValueObject\TestPlainValueObject;
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
