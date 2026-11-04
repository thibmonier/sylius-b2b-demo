<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\DataFixtures;

use App\Demo\DataFixtures\Passport\PassportSheet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** US-235: a passport fixture without a mandatory field must fail the load and name the field, never store a default text. */
final class PassportSheetTest extends TestCase
{
    public function testACompleteSheetKeepsItsThreeFields(): void
    {
        $sheet = PassportSheet::fromArray('OUTI-026', ['originMaterials' => 'a', 'recyclability' => 'b', 'repairability' => 'c']);

        self::assertSame(['a', 'b', 'c'], [$sheet->originMaterials, $sheet->recyclability, $sheet->repairability]);
    }

    /** @return iterable<string, array{string, array<string, string>}> */
    public static function incompleteSheets(): iterable
    {
        $complete = ['originMaterials' => 'a', 'recyclability' => 'b', 'repairability' => 'c'];

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
}
