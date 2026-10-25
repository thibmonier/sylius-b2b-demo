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

    public function testEverySkuOfTheCatalogueBelongsToExactlyOneTypePhotographedNeutralOrPending(): void
    {
        $listed = [
            ...\array_merge(...\array_map(static fn ($photo): array => $photo->skus, $this->selection->photos())),
            ...\array_merge([], ...\array_column($this->selection->neutral(), 'skus')),
            ...$this->selection->pendingSkus(),
        ];

        $catalogue = $this->catalogueSkus();
        \sort($catalogue);
        \sort($listed);
        self::assertSame($catalogue, $listed, 'A SKU missing from the selection, listed twice, or unknown to the catalogue.');
    }

    public function testEveryPhotoNamesItsAuthorItsPageAndItsLicence(): void
    {
        self::assertNotEmpty($this->selection->photos());
        $banks = ['Pexels' => ['#^https://www\.pexels\.com/photo/\d+/$#', 'https://www.pexels.com/license/'], 'Unsplash' => ['#^https://unsplash\.com/photos/[\w-]+$#', 'https://unsplash.com/license']];
        foreach ($this->selection->photos() as $photo) {
            self::assertArrayHasKey($photo->source, $banks, $photo->type);
            self::assertNotSame('', $photo->creator, $photo->type . ': creator');
            self::assertNotSame('', $photo->title, $photo->type . ': title');
            self::assertMatchesRegularExpression($banks[$photo->source][0], $photo->originUrl, $photo->type);
            self::assertSame($banks[$photo->source][1], $photo->licenseUrl, $photo->type);
            self::assertMatchesRegularExpression('/^(full-\d+|[up]-[\w-]+)\.jpg$/', $photo->file, $photo->type);
        }
    }

    public function testTheNeutralTypesNameAnExistingCategoryVisual(): void
    {
        foreach ($this->selection->neutral() as $type) {
            self::assertMatchesRegularExpression('/^category-[\w-]+\.webp$/', $type['file'], $type['type']);
            self::assertFileExists(\dirname(__DIR__, 4) . '/fixtures/images/' . $type['file'], $type['type']);
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

    public function testTheFocusStaysInsideThePhoto(): void
    {
        foreach ($this->selection->photos() as $photo) {
            self::assertGreaterThanOrEqual(0.0, $photo->focusX, $photo->type);
            self::assertLessThanOrEqual(1.0, $photo->focusX, $photo->type);
            self::assertGreaterThanOrEqual(0.0, $photo->focusY, $photo->type);
            self::assertLessThanOrEqual(1.0, $photo->focusY, $photo->type);
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
