<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Images;

use App\Demo\Images\Fetch\ImageProcessor;
use App\Demo\Images\ImageManifest;
use App\Demo\Images\ImageManifestAuditor;
use App\Demo\Images\Import\PhotoCropper;
use App\Demo\Images\Import\PhotoSelection;
use App\Demo\Images\Import\SelectionImporter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/** US-196: the photos supplied as local files are imported without any network call, the same way every time. */
final class SelectionImporterTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = \sys_get_temp_dir() . '/photo-import-' . \uniqid('', true);
        (new Filesystem())->mkdir([$this->dir . '/source', $this->dir . '/images']);
        $this->jpeg($this->dir . '/source/full-1.jpg', 1600, 1200, 0xCC3333);
        $this->jpeg($this->dir . '/source/full-2.jpg', 1000, 1500, 0x3366CC);
        \file_put_contents($this->dir . '/images/orphan0000aa.webp', 'old');
        \file_put_contents($this->dir . '/images/category-gants.webp', 'neutral');
        \file_put_contents($this->dir . '/images/manifest.csv', "sku,file,source,creator,title,origin_url,license,license_url,fetched_at,scope,status\n"
            . "P-1,orphan0000aa.webp,flickr,Jane,Old,https://example.test/1,cc0,https://creativecommons.org/publicdomain/zero/1.0/,2026-10-17,family,validated\n"
            . "P-2,orphan0000aa.webp,flickr,Jane,Old,https://example.test/1,cc0,https://creativecommons.org/publicdomain/zero/1.0/,2026-10-17,family,validated\n"
            . "P-9,category-gants.webp,generated,Thibaut,Neutral,https://example.test/9,cc0,https://creativecommons.org/publicdomain/zero/1.0/,2026-10-17,category,fallback\n");
        \file_put_contents($this->dir . '/selection.json', \json_encode([
            'photos' => [
                $this->photo('casque', ['P-1', 'P-2'], 'full-1.jpg', 'Hard hat'),
                $this->photo('vis', ['P-3'], 'full-2.jpg', 'Bolts'),
            ],
            'pending' => [['type' => 'gants', 'name_fr' => 'Gants', 'skus' => ['P-9'], 'reason' => 'new search']],
        ], \JSON_THROW_ON_ERROR));
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->dir);
    }

    public function testEveryTypeGetsOneFourByThreeWebpSharedByItsSkusAndTheManifestFollows(): void
    {
        $report = $this->import();

        $manifest = ImageManifest::load($this->dir . '/images/manifest.csv');
        self::assertSame($manifest->fileFor('P-1'), $manifest->fileFor('P-2'));
        self::assertNotSame($manifest->fileFor('P-1'), $manifest->fileFor('P-3'));
        self::assertSame(['family', 'family', 'own'], [($manifest->row('P-1') ?? [])['scope'], ($manifest->row('P-2') ?? [])['scope'], ($manifest->row('P-3') ?? [])['scope']]);
        self::assertSame(['pexels', 'pexels', 'https://www.pexels.com/license/', '2026-10-25', 'validated'], [
            ($manifest->row('P-1') ?? [])['source'], ($manifest->row('P-1') ?? [])['license'], ($manifest->row('P-1') ?? [])['license_url'], ($manifest->row('P-1') ?? [])['fetched_at'], ($manifest->row('P-1') ?? [])['status'],
        ]);
        foreach (['P-1', 'P-3'] as $sku) {
            $path = $this->dir . '/images/' . $manifest->fileFor($sku);
            self::assertLessThanOrEqual(ImageProcessor::MAX_BYTES, \filesize($path));
            $size = \getimagesize($path);
            self::assertIsArray($size);
            self::assertSame(4 / 3, $size[0] / $size[1], $sku);
        }
        self::assertSame(['photos' => 2, 'skus' => 3], ['photos' => $report['photos'], 'skus' => $report['skus']]);
        self::assertSame([], (new ImageManifestAuditor())->violations(\array_filter($manifest->rows(), static fn (array $row): bool => $row['source'] === 'pexels')));
    }

    public function testTheOrphanPhotosAreRemovedButNeutralVisualsAndPendingTypesKeepTheirImage(): void
    {
        $report = $this->import();

        self::assertSame(['orphan0000aa.webp'], $report['removed']);
        self::assertFileDoesNotExist($this->dir . '/images/orphan0000aa.webp');
        self::assertFileExists($this->dir . '/images/category-gants.webp');
        self::assertSame('category-gants.webp', ImageManifest::load($this->dir . '/images/manifest.csv')->fileFor('P-9'));
    }

    public function testTheSameInputGivesTheSameFileNames(): void
    {
        $this->import();
        $first = ImageManifest::load($this->dir . '/images/manifest.csv')->files();
        $this->import();

        self::assertEqualsCanonicalizing($first, ImageManifest::load($this->dir . '/images/manifest.csv')->files());
    }

    public function testAMissingSourceFileStopsTheImportBeforeAnythingIsWritten(): void
    {
        \unlink($this->dir . '/source/full-2.jpg');

        try {
            $this->import();
            self::fail('The missing file must stop the import.');
        } catch (\RuntimeException $exception) {
            self::assertStringContainsString('full-2.jpg', $exception->getMessage());
        }
        self::assertFileExists($this->dir . '/images/orphan0000aa.webp');
        self::assertCount(2, \glob($this->dir . '/images/*.webp') ?: []);
    }

    public function testAnEmptyOrRootSourceFolderIsRefusedBeforeAnythingIsWritten(): void
    {
        foreach (['', '/', $this->dir . '/missing'] as $source) {
            try {
                $this->importFrom($source);
                self::fail('The source "' . $source . '" must be refused.');
            } catch (\RuntimeException $exception) {
                self::assertStringContainsString('source', $exception->getMessage());
            }
        }
        self::assertFileExists($this->dir . '/images/orphan0000aa.webp');
        self::assertCount(2, \glob($this->dir . '/images/*.webp') ?: []);
    }

    public function testASourceFolderWithNoneOfTheSelectedPhotosIsRefused(): void
    {
        \mkdir($this->dir . '/other');

        $this->expectException(\RuntimeException::class);
        $this->importFrom($this->dir . '/other');
    }

    public function testOnlyTheWebpFilesOfTheImageFolderItselfAreEverRemoved(): void
    {
        \mkdir($this->dir . '/images/nested');
        \file_put_contents($this->dir . '/images/nested/keep0000bb.webp', 'x');
        \file_put_contents($this->dir . '/images/notes.txt', 'x');

        $this->import();

        self::assertFileExists($this->dir . '/images/nested/keep0000bb.webp');
        self::assertFileExists($this->dir . '/images/notes.txt');
    }

    /** @return array{photos: int, skus: int, removed: list<string>} */
    private function import(): array
    {
        return $this->importFrom($this->dir . '/source');
    }

    /** @return array{photos: int, skus: int, removed: list<string>} */
    private function importFrom(string $source): array
    {
        $manifest = ImageManifest::load($this->dir . '/images/manifest.csv');

        return (new SelectionImporter(new ImageProcessor(), new PhotoCropper()))->import(
            PhotoSelection::load($this->dir . '/selection.json'),
            $source,
            $this->dir . '/images',
            $manifest,
            '2026-10-25',
        );
    }

    /**
     * @param list<string> $skus
     *
     * @return array<string, mixed>
     */
    private function photo(string $type, array $skus, string $file, string $title): array
    {
        return ['type' => $type, 'name_fr' => $title, 'skus' => $skus, 'file' => $file, 'title' => $title, 'creator' => 'Jane Doe', 'creator_url' => 'https://www.pexels.com/@jane/',
            'origin_url' => 'https://www.pexels.com/photo/123/', 'source' => 'Pexels', 'license_url' => 'https://www.pexels.com/license/', 'focus' => [0.5, 0.5], 'blur' => [], 'review' => 'ok'];
    }

    /**
     * @param int<1, max> $width
     * @param int<1, max> $height
     */
    private function jpeg(string $path, int $width, int $height, int $colour): void
    {
        $image = \imagecreatetruecolor($width, $height);
        \imagefill($image, 0, 0, $colour);
        \imagejpeg($image, $path, 90);
    }
}
