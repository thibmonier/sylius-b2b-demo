<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\DataFixtures;

use App\Demo\DataFixtures\Passport\Gtin;
use App\Demo\DataFixtures\Passport\PassportSheet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** US-235: a passport fixture without a mandatory field must fail the load and name the field, never store a default text. */
final class PassportSheetTest extends TestCase
{
    public function testACompleteSheetKeepsItsThreeFields(): void
    {
        $sheet = PassportSheet::fromArray('OUTI-026', self::complete());

        self::assertSame(['09506000000015', 'a', 'b', 'c'], [$sheet->gtin, $sheet->originMaterials, $sheet->recyclability, $sheet->repairability]);
    }

    /** @return array<string, string> */
    private static function complete(): array
    {
        return ['gtin' => '09506000000015', 'originMaterials' => 'a', 'recyclability' => 'b', 'repairability' => 'c'];
    }

    /** @return iterable<string, array{string, array<string, string>}> */
    public static function incompleteSheets(): iterable
    {
        $complete = self::complete();

        foreach (array_keys($complete) as $field) {
            $missing = $complete;
            unset($missing[$field]);
            yield $field . ' missing' => [$field, $missing];

            yield $field . ' blank' => [$field, [$field => "  \n"] + $complete];
        }
    }

    /** @param array<string, string> $data */
    #[DataProvider('incompleteSheets')]
    public function testAnIncompleteSheetFailsAndNamesTheMissingField(string $field, array $data): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('"OUTI-026": required field "%s"', $field));

        PassportSheet::fromArray('OUTI-026', $data);
    }

    public function testAGtinWithAWrongCheckDigitFailsAndNamesTheProductAndTheGtin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"OUTI-026": GTIN "09506000000016" is not valid');

        PassportSheet::fromArray('OUTI-026', ['gtin' => '09506000000016'] + self::complete());
    }

    public function testAGtinOutsideTheGs1ExamplePrefixFailsAndNamesTheProductAndTheGtin(): void
    {
        $realLooking = Gtin::withCheckDigit('3017620422003');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('"OUTI-026": GTIN "%s" must start with the GS1 example prefix "0950600"', $realLooking));

        PassportSheet::fromArray('OUTI-026', ['gtin' => $realLooking] + self::complete());
    }
}
