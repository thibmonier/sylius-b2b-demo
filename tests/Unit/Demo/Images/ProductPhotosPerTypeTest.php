<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Images;

use App\Demo\Images\ImageManifest;
use App\Demo\Images\ImageManifestAuditor;
use App\Demo\Images\Import\PhotoSelection;
use PHPUnit\Framework\TestCase;

/** US-196: each product shows the photo of its own type of article; no photo is shared between two types. */
final class ProductPhotosPerTypeTest extends TestCase
{
    private string $imagesDir;

    private PhotoSelection $selection;

    private ImageManifest $manifest;

    protected function setUp(): void
    {
        $this->imagesDir = \dirname(__DIR__, 4) . '/fixtures/images';
        $this->selection = PhotoSelection::load($this->imagesDir . '/selection.json');
        $this->manifest = ImageManifest::load($this->imagesDir . '/manifest.csv');
    }

    public function testEverySkuPointsToThePhotoOfItsType(): void
    {
        $wrong = [];
        foreach ($this->selection->photos() as $photo) {
            $files = \array_unique(\array_map(fn (string $sku): ?string => $this->manifest->fileFor($sku), $photo->skus));
            if (\count($files) !== 1 || \str_starts_with((string) $files[0], 'category-') || $files[0] === null) {
                $wrong[] = $photo->type;
            }
        }

        self::assertSame([], $wrong, 'Types whose SKUs do not all show one photo of their own.');
    }

    public function testNoPhotoIsSharedBetweenTwoDifferentTypes(): void
    {
        $owners = [];
        foreach ($this->selection->photos() as $photo) {
            $owners[(string) $this->manifest->fileFor($photo->skus[0])][] = $photo->type;
        }

        $shared = \array_filter($owners, static fn (array $types): bool => \count($types) > 1);
        self::assertSame([], $shared, 'A file shown by SKUs of different types.');
    }

    public function testThePendingTypesDoNotShareTheirImageWithASelectedType(): void
    {
        $selectedFiles = [];
        foreach ($this->selection->photos() as $photo) {
            $selectedFiles[(string) $this->manifest->fileFor($photo->skus[0])] = true;
        }

        $clashes = \array_filter($this->selection->pendingSkus(), fn (string $sku): bool => isset($selectedFiles[(string) $this->manifest->fileFor($sku)]));
        self::assertSame([], \array_values($clashes));
    }

    public function testThePhotosAreLicensedTracedAndInTheFrameOfTheProductPage(): void
    {
        foreach ($this->selection->photos() as $photo) {
            $row = $this->manifest->row($photo->skus[0]);
            self::assertNotNull($row, $photo->type);
            self::assertSame([$photo->creator, $photo->originUrl, $photo->licenseUrl, 'pexels', 'validated'], [$row['creator'], $row['origin_url'], $row['license_url'], $row['license'], $row['status']], $photo->type);
            $size = \getimagesize($this->imagesDir . '/' . $row['file']);
            self::assertIsArray($size, $row['file']);
            self::assertEqualsWithDelta(4 / 3, $size[0] / $size[1], 0.01, $row['file'] . ' is not 4:3');
            self::assertSame([], (new ImageManifestAuditor())->violations([$row]));
        }
    }
}
