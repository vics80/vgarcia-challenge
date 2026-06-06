<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Domain\Money;

use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Exception\InvalidCoinException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CoinTest extends TestCase
{
    #[DataProvider('acceptedDecimalValuesProvider')]
    public function testCreatesCoinFromAcceptedDecimalString(string $value, Coin $expected): void
    {
        self::assertSame($expected, Coin::fromDecimalString($value));
    }

    #[DataProvider('coinValuesProvider')]
    public function testExposesCentsAndDecimalString(Coin $coin, int $cents, string $decimalString): void
    {
        self::assertSame($cents, $coin->cents());
        self::assertSame($decimalString, $coin->decimalString());
    }

    public function testFailsWhenDecimalStringIsNotAccepted(): void
    {
        $this->expectException(InvalidCoinException::class);
        $this->expectExceptionMessage('Coin [0.50] is not accepted by this vending machine.');

        Coin::fromDecimalString('0.50');
    }

    public static function acceptedDecimalValuesProvider(): iterable
    {
        yield 'five cents' => ['0.05', Coin::FIVE_CENTS];
        yield 'ten cents with two decimals' => ['0.10', Coin::TEN_CENTS];
        yield 'ten cents with one decimal' => ['0.1', Coin::TEN_CENTS];
        yield 'twenty five cents' => ['0.25', Coin::TWENTY_FIVE_CENTS];
        yield 'one euro with two decimals' => ['1.00', Coin::ONE_EURO];
        yield 'one euro with one decimal' => ['1.0', Coin::ONE_EURO];
        yield 'one euro without decimals' => ['1', Coin::ONE_EURO];
        yield 'trimmed value' => [' 0.25 ', Coin::TWENTY_FIVE_CENTS];
    }

    public static function coinValuesProvider(): iterable
    {
        yield 'five cents' => [Coin::FIVE_CENTS, 5, '0.05'];
        yield 'ten cents' => [Coin::TEN_CENTS, 10, '0.10'];
        yield 'twenty five cents' => [Coin::TWENTY_FIVE_CENTS, 25, '0.25'];
        yield 'one euro' => [Coin::ONE_EURO, 100, '1.00'];
    }
}
