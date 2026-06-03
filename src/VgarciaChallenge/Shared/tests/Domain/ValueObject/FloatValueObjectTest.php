<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Domain\ValueObject;

use App\VgarciaChallenge\Shared\Domain\Specification\Exception\NumberMinMaxException;
use App\Tests\VgarciaChallenge\Shared\Support\ValueObject\TestLimitedFloatValueObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FloatValueObjectTest extends TestCase
{
    public function testCreateReturnsNullWhenValueIsNull(): void
    {
        self::assertNull(TestLimitedFloatValueObject::create(null));
    }

    #[DataProvider('validFloatValuesProvider')]
    public function testDoesNotFailWhenValueIsWithinRange(float $value): void
    {
        $valueObject = new TestLimitedFloatValueObject($value);

        self::assertSame($value, $valueObject->value());
        self::assertSame((string) $value, (string) $valueObject);
    }

    #[DataProvider('invalidFloatValuesProvider')]
    public function testFailsWhenValueIsOutsideRange(float $value): void
    {
        $this->expectException(NumberMinMaxException::class);

        new TestLimitedFloatValueObject($value);
    }

    #[DataProvider('floatEqualityProvider')]
    public function testEqualsReturnsExpectedValue(float $left, float $right, bool $expected): void
    {
        self::assertSame(
            $expected,
            (new TestLimitedFloatValueObject($left))->equals(new TestLimitedFloatValueObject($right))
        );
    }

    public static function validFloatValuesProvider(): iterable
    {
        yield 'middle value' => [5.5];
        yield 'minimum boundary' => [1.5];
        yield 'maximum boundary' => [10.5];
    }

    public static function invalidFloatValuesProvider(): iterable
    {
        yield 'below minimum' => [1.4];
        yield 'above maximum' => [10.6];
    }

    public static function floatEqualityProvider(): iterable
    {
        yield 'same value' => [5.5, 5.5, true];
        yield 'different value' => [5.5, 5.6, false];
    }
}
