<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Domain\ValueObject;

use App\VgarciaChallenge\Shared\Domain\Specification\Exception\MinMaxException;
use App\Tests\VgarciaChallenge\Shared\Support\ValueObject\TestLimitedStringValueObject;
use App\Tests\VgarciaChallenge\Shared\Support\ValueObject\TestOtherLimitedStringValueObject;
use App\Tests\VgarciaChallenge\Shared\Support\ValueObject\TestStringableValue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StringValueObjectTest extends TestCase
{
    public function testCreateReturnsNullWhenValueIsNull(): void
    {
        self::assertNull(TestLimitedStringValueObject::create(null));
    }

    #[DataProvider('validStringValuesProvider')]
    public function testDoesNotFailWhenValueLengthIsWithinLimit(string $value): void
    {
        $valueObject = new TestLimitedStringValueObject($value);

        self::assertSame($value, $valueObject->value());
        self::assertSame($value, (string) $valueObject);
    }

    public function testDoesNotFailWhenValueImplementsStringable(): void
    {
        $valueObject = new TestLimitedStringValueObject(new TestStringableValue('abcd'));

        self::assertSame('abcd', $valueObject->value());
    }

    #[DataProvider('invalidStringValuesProvider')]
    public function testFailsWhenValueLengthExceedsMaximum(string $value): void
    {
        $this->expectException(MinMaxException::class);

        new TestLimitedStringValueObject($value);
    }

    #[DataProvider('sameClassEqualityProvider')]
    public function testEqualsReturnsExpectedValueForSameClass(string $left, string $right, bool $expected): void
    {
        $first = new TestLimitedStringValueObject($left);
        $second = new TestLimitedStringValueObject($right);

        self::assertSame($expected, $first->equals($second));
    }

    #[DataProvider('differentClassEqualityProvider')]
    public function testEqualsReturnsFalseForDifferentClass(string $value): void
    {
        $first = new TestLimitedStringValueObject($value);
        $second = new TestOtherLimitedStringValueObject($value);

        self::assertFalse($first->equals($second));
    }

    public static function validStringValuesProvider(): iterable
    {
        yield 'max length value' => ['abcd'];
        yield 'short value' => ['ab'];
        yield 'empty string' => [''];
    }

    public static function invalidStringValuesProvider(): iterable
    {
        yield 'five chars' => ['abcde'];
        yield 'longer text' => ['abcdef'];
    }

    public static function sameClassEqualityProvider(): iterable
    {
        yield 'same value' => ['abcd', 'abcd', true];
        yield 'different value' => ['abcd', 'abce', false];
    }

    public static function differentClassEqualityProvider(): iterable
    {
        yield 'same underlying value' => ['abcd'];
        yield 'another underlying value' => ['ab'];
    }
}
