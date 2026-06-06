<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type\MoneyType;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use PHPUnit\Framework\TestCase;

final class MoneyTypeTest extends TestCase
{
    public function testReturnsJsonSqlDeclaration(): void
    {
        self::assertNotSame('', (new MoneyType())->getSQLDeclaration([], new SQLitePlatform()));
    }

    public function testConvertsValuesToDatabaseValue(): void
    {
        $type = new MoneyType();
        $platform = new SQLitePlatform();
        $money = Money::fromCoins(Coin::FIVE_CENTS, Coin::TEN_CENTS);

        self::assertNull($type->convertToDatabaseValue(null, $platform));
        self::assertSame(
            '[{"coinCents":5,"quantity":1},{"coinCents":10,"quantity":1}]',
            $type->convertToDatabaseValue($money, $platform),
        );
        self::assertSame('[{"coinCents":25,"quantity":2}]', $type->convertToDatabaseValue([
            ['coinCents' => 25, 'quantity' => 2],
        ], $platform));
    }

    public function testConvertsValuesToPHPValue(): void
    {
        $type = new MoneyType();
        $platform = new SQLitePlatform();
        $money = Money::fromCoins(Coin::FIVE_CENTS);

        self::assertNull($type->convertToPHPValue(null, $platform));
        self::assertSame($money, $type->convertToPHPValue($money, $platform));
        self::assertSame(25, $type->convertToPHPValue('[{"coinCents":25,"quantity":1}]', $platform)?->totalCents());
        self::assertSame(200, $type->convertToPHPValue([
            ['coinCents' => 100, 'quantity' => 2],
        ], $platform)?->totalCents());
    }
}
