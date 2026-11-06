<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\DataFixtures;

use App\Demo\DataFixtures\Passport\Gtin;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** US-272: the demo GTIN come with a GS1 mod-10 check digit that is computed and verified, never typed blindly. */
final class GtinTest extends TestCase
{
    /** @return iterable<string, array{string, int}> */
    public static function officialExamples(): iterable
    {
        yield 'GS1 Digital Link example GTIN-14' => ['0950600013435', 2];
        yield 'GTIN-13 of the GS1 manual' => ['629104150021', 3];
        yield 'GTIN-8' => ['9638507', 4];
        yield 'all zeros' => ['0000000', 0];
    }

    #[DataProvider('officialExamples')]
    public function testTheCheckDigitFollowsTheGs1Algorithm(string $body, int $expected): void
    {
        self::assertSame($expected, Gtin::checkDigit($body));
    }

    public function testWithCheckDigitAppendsTheKey(): void
    {
        self::assertSame('09506000134352', Gtin::withCheckDigit('0950600013435'));
    }

    public function testAGtinWithTheRightKeyIsValid(): void
    {
        self::assertTrue(Gtin::isValid('09506000134352'));
    }

    /** @return iterable<string, array{string}> */
    public static function invalidGtins(): iterable
    {
        yield 'wrong key' => ['09506000134353'];
        yield 'too short' => ['0950600'];
        yield 'nine digits' => ['095060001'];
        yield 'letters' => ['0950600013435X'];
        yield 'empty' => [''];
    }

    #[DataProvider('invalidGtins')]
    public function testAnInvalidGtinIsRefused(string $gtin): void
    {
        self::assertFalse(Gtin::isValid($gtin));
    }
}
