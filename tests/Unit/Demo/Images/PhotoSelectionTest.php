<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Images;

use App\Demo\Images\Import\PhotoSelection;
use App\Demo\Infrastructure\Csv\CsvProductReader;
use PHPUnit\Framework\TestCase;

/** US-196: the selection of photos (one per type of article, chosen by Thibaut) is complete, licensed and traced. */
final class PhotoSelectionTest extends TestCase
{
    private PhotoSelection $selection;

    protected function setUp(): void
    {
        $this->selection = PhotoSelection::load(\dirname(__DIR__, 4) . '/fixtures/images/selection.json');
    }

    public function testEverySkuOfTheCatalogueBelongsToExactlyOneTypeSelectedOrPending(): void
    {
        $listed = [...\array_merge(...\array_map(static fn ($photo): array => $photo->skus, $this->selection->photos())), ...$this->selection->pendingSkus()];

        $catalogue = $this->catalogueSkus();
        \sort($catalogue);
        \sort($listed);
        self::assertSame($catalogue, $listed, 'A SKU missing from the selection, listed twice, or unknown to the catalogue.');
    }

    public function testEveryPhotoNamesItsAuthorItsPexelsPageAndItsLicence(): void
    {
        self::assertNotEmpty($this->selection->photos());
        foreach ($this->selection->photos() as $photo) {
            self::assertNotSame('', $photo->creator, $photo->type . ': creator');
            self::assertNotSame('', $photo->title, $photo->type . ': title');
            self::assertMatchesRegularExpression('#^https://www\.pexels\.com/photo/\d+/$#', $photo->originUrl, $photo->type);
            self::assertSame('https://www.pexels.com/license/', $photo->licenseUrl, $photo->type);
            self::assertMatchesRegularExpression('/^full-\d+\.jpg$/', $photo->file, $photo->type);
        }
    }

    public function testNoPhotoIsUsedForTwoTypesAndAPhotoWithABrandIsNeverSelected(): void
    {
        $files = \array_map(static fn ($photo): string => $photo->file, $this->selection->photos());

        self::assertSame($files, \array_values(\array_unique($files)), 'One photo selected for two types.');
        foreach ($this->selection->photos() as $photo) {
            self::assertContains($photo->review, ['ok', 'reserve'], $photo->type . ': a photo with a legible brand is replaced, not selected');
        }
    }

    public function testTheFocusAndTheBlurredAreasStayInsideThePhoto(): void
    {
        foreach ($this->selection->photos() as $photo) {
            self::assertGreaterThanOrEqual(0.0, $photo->focusX, $photo->type);
            self::assertLessThanOrEqual(1.0, $photo->focusX, $photo->type);
            self::assertGreaterThanOrEqual(0.0, $photo->focusY, $photo->type);
            self::assertLessThanOrEqual(1.0, $photo->focusY, $photo->type);
            foreach ($photo->blurs as [$x, $y, $width, $height]) {
                self::assertGreaterThanOrEqual(0.0, $x, $photo->type);
                self::assertGreaterThanOrEqual(0.0, $y, $photo->type);
                self::assertLessThanOrEqual(1.0, $x + $width, $photo->type);
                self::assertLessThanOrEqual(1.0, $y + $height, $photo->type);
            }
        }
    }

    /** @return list<string> */
    private function catalogueSkus(): array
    {
        $skus = [];
        foreach (['epi', 'quincaillerie', 'fournitures'] as $name) {
            foreach ((new CsvProductReader())->read(\dirname(__DIR__, 4) . '/fixtures/csv/' . $name . '.csv') as $row) {
                $skus[] = $row['sku'];
            }
        }

        return $skus;
    }
}
