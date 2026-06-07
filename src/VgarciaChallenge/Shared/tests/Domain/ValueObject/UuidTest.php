<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Domain\ValueObject;

use App\Tests\VgarciaChallenge\Shared\Support\ValueObject\TestUuidValueObject;
use App\VgarciaChallenge\Shared\Domain\ValueObject\Exception\InvalidUuidException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UuidTest extends TestCase
{
    public function testCreateReturnsNullWhenValueIsNull(): void
    {
        self::assertNull(TestUuidValueObject::create(null));
    }

    #[DataProvider('validUuidProvider')]
    public function testDoesNotFailWhenUuidIsValid(string $uuid): void
    {
        $valueObject = new TestUuidValueObject($uuid);

        self::assertSame($uuid, $valueObject->value());
        self::assertSame($uuid, (string) $valueObject);
    }

    #[DataProvider('invalidUuidProvider')]
    public function testFailsWhenUuidIsInvalid(string $uuid): void
    {
        $this->expectException(InvalidUuidException::class);

        new TestUuidValueObject($uuid);
    }

    #[DataProvider('validUuidProvider')]
    public function testValidateReturnsTrueForValidUuid(string $uuid): void
    {
        self::assertTrue(TestUuidValueObject::validate($uuid));
    }

    #[DataProvider('invalidUuidProvider')]
    public function testValidateReturnsFalseForInvalidUuid(string $uuid): void
    {
        self::assertFalse(TestUuidValueObject::validate($uuid));
    }

    public function testRandomReturnsValidUuidInstance(): void
    {
        $valueObject = TestUuidValueObject::random();

        self::assertInstanceOf(TestUuidValueObject::class, $valueObject);
        self::assertTrue(TestUuidValueObject::validate($valueObject->value()));
    }

    #[DataProvider('uuidEqualityProvider')]
    public function testEqualsReturnsExpectedValue(string $left, string $right, bool $expected): void
    {
        self::assertSame(
            $expected,
            (new TestUuidValueObject($left))
                ->equals(new TestUuidValueObject($right))
        );
    }

    public static function validUuidProvider(): iterable
    {
        yield 'uuid v7 sample' => ['018f47d2-7c6a-7caa-b9d4-8b22f1c6d001'];
        yield 'another valid uuid' => ['018f47d2-7c6a-7caa-b9d4-8b22f1c6d002'];
    }

    public static function invalidUuidProvider(): iterable
    {
        yield 'plain text' => ['invalid-uuid'];
        yield 'empty string' => [''];
    }

    public static function uuidEqualityProvider(): iterable
    {
        yield 'same uuid' => ['018f47d2-7c6a-7caa-b9d4-8b22f1c6d001', '018f47d2-7c6a-7caa-b9d4-8b22f1c6d001', true];
        yield 'different uuid' => ['018f47d2-7c6a-7caa-b9d4-8b22f1c6d001', '018f47d2-7c6a-7caa-b9d4-8b22f1c6d002', false];
    }
}
