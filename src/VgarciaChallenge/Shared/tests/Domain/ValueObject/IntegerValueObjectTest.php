<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Domain\ValueObject;

use App\Tests\VgarciaChallenge\Shared\Support\ValueObject\TestLimitedIntegerValueObject;
use App\VgarciaChallenge\Shared\Domain\Specification\Exception\NumberMinMaxException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IntegerValueObjectTest extends TestCase
{
    public function testCreateReturnsNullWhenValueIsNull(): void
    {
        self::assertNull(TestLimitedIntegerValueObject::create(null));
    }

    #[DataProvider('validIntegerValuesProvider')]
    public function testDoesNotFailWhenValueIsWithinRange(int $value): void
    {
        $valueObject = new TestLimitedIntegerValueObject($value);

        self::assertSame($value, $valueObject->value());
        self::assertSame((string) $value, (string) $valueObject);
    }

    #[DataProvider('invalidIntegerValuesProvider')]
    public function testFailsWhenValueIsOutsideRange(int $value): void
    {
        $this->expectException(NumberMinMaxException::class);

        new TestLimitedIntegerValueObject($value);
    }

    #[DataProvider('integerEqualityProvider')]
    public function testEqualsReturnsExpectedValue(int $left, int $right, bool $expected): void
    {
        self::assertSame(
            $expected,
            (new TestLimitedIntegerValueObject($left))->equals(new TestLimitedIntegerValueObject($right))
        );
    }

    public static function validIntegerValuesProvider(): iterable
    {
        yield 'middle value' => [5];
        yield 'minimum boundary' => [1];
        yield 'maximum boundary' => [10];
    }

    public static function invalidIntegerValuesProvider(): iterable
    {
        yield 'below minimum' => [0];
        yield 'above maximum' => [11];
    }

    public static function integerEqualityProvider(): iterable
    {
        yield 'same value' => [5, 5, true];
        yield 'different value' => [5, 6, false];
    }
}
