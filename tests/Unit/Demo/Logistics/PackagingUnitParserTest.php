<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Logistics;

use App\Demo\Logistics\PackagingUnitParser;
use PHPUnit\Framework\TestCase;

final class PackagingUnitParserTest extends TestCase
{
    /** @dataProvider packagingUnits */
    public function testItReadsTheNumberOfSoldUnitsPerCase(?string $packagingUnit, int $expected): void
    {
        self::assertSame($expected, (new PackagingUnitParser())->unitsPerCase($packagingUnit));
    }

    /** @return iterable<string, array{?string, int}> */
    public static function packagingUnits(): iterable
    {
        yield 'box of 100' => ['boîte de 100', 100];
        yield 'lot of 25' => ['lot de 25', 25];
        yield 'pack of 500' => ['paquet de 500', 500];
        yield 'roll of 500' => ['rouleau de 500', 500];
        yield 'bag of 200' => ['sachet de 200', 200];
        yield 'unit' => ['unité', 1];
        yield 'pair' => ['paire', 1];
        yield 'bare roll' => ['rouleau', 1];
        yield 'empty' => ['', 1];
        yield 'absent' => [null, 1];
        yield 'zero is not a case' => ['boîte de 0', 1];
    }

    /** @dataProvider casePrefixes */
    public function testItTellsBoxesAndLotsFromOtherPackagings(?string $packagingUnit, string $expected): void
    {
        self::assertSame($expected, (new PackagingUnitParser())->kind($packagingUnit));
    }

    /** @return iterable<string, array{?string, string}> */
    public static function casePrefixes(): iterable
    {
        yield 'box' => ['boîte de 100', 'box'];
        yield 'lot' => ['lot de 25', 'lot'];
        yield 'roll' => ['rouleau de 500', 'other'];
        yield 'unit' => ['unité', 'other'];
        yield 'absent' => [null, 'other'];
    }
}
