<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Images;

use App\Demo\Images\Fetch\ImageProcessor;
use App\Demo\Images\ImageManifest;
use App\Demo\Images\ImageManifestAuditor;
use App\Demo\Infrastructure\Csv\CsvProductReader;
use PHPUnit\Framework\TestCase;

/** US-047 guard: the images committed to the repository stay light, CC0, traced and complete. */
final class ImageBudgetTest extends TestCase
{
    private const TOTAL_BUDGET_BYTES = 15_000_000;

    private string $imagesDir;

    protected function setUp(): void
    {
        $this->imagesDir = \dirname(__DIR__, 4) . '/fixtures/images';
    }

    public function testTheImagesStayUnderTheBudgetOfTheDirectoryAndOfEachImage(): void
    {
        $total = 0;
        foreach ($this->imageFiles() as $file) {
            $size = (int) \filesize($file);
            $total += $size;
            self::assertLessThanOrEqual(ImageProcessor::MAX_BYTES, $size, \basename($file) . ' is over the per-image budget.');
        }

        self::assertLessThanOrEqual(self::TOTAL_BUDGET_BYTES, $total, 'fixtures/images is over the total budget.');
    }

    public function testEveryManifestRowIsCc0AndFullyFilled(): void
    {
        $rows = $this->manifest()->rows();

        self::assertNotEmpty($rows);
        self::assertSame([], (new ImageManifestAuditor())->violations($rows), 'Rows that are not free, not reviewed or not fit for their product (US-092).');
    }

    public function testEveryProductOfTheCatalogueHasAnImage(): void
    {
        $reader = new CsvProductReader();
        $missing = [];
        foreach (['epi', 'quincaillerie', 'fournitures'] as $name) {
            foreach ($reader->read(\dirname($this->imagesDir) . '/csv/' . $name . '.csv') as $row) {
                if ($this->manifest()->fileFor($row['sku']) === null) {
                    $missing[] = $row['sku'];
                }
            }
        }

        self::assertSame([], $missing, 'SKUs without an image (own or family).');
    }

    public function testNoManifestRowPointsToAProductThatNoLongerExists(): void
    {
        $skus = [];
        foreach (['epi', 'quincaillerie', 'fournitures'] as $name) {
            foreach ((new CsvProductReader())->read(\dirname($this->imagesDir) . '/csv/' . $name . '.csv') as $row) {
                $skus[] = $row['sku'];
            }
        }

        self::assertSame([], \array_values(\array_diff($this->manifest()->skus(), $skus)), 'Manifest rows of SKUs absent from the CSV files.');
    }

    public function testTheManifestAndTheFolderListTheSameFiles(): void
    {
        $onDisk = \array_map('basename', $this->imageFiles());
        $listed = $this->manifest()->files();

        \sort($onDisk);
        \sort($listed);
        self::assertSame($listed, $onDisk, 'An image without a manifest row, or a row without its image.');
    }

    public function testNothingOutsideTheManualScriptTalksToTheImageBank(): void
    {
        $root = \dirname($this->imagesDir, 2);
        $offenders = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root . '/src', \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            \assert($file instanceof \SplFileInfo);
            if ($file->isFile() && !\str_contains($file->getPathname(), '/Demo/Images/Fetch/') && \stripos((string) \file_get_contents($file->getPathname()), 'openverse') !== false) {
                $offenders[] = $file->getPathname();
            }
        }
        foreach (['config', 'docker', 'Dockerfile', '.github'] as $path) {
            if (\file_exists($root . '/' . $path)) {
                $offenders = [...$offenders, ...$this->grep($root . '/' . $path)];
            }
        }

        self::assertSame([], $offenders, 'Only src/Demo/Images/Fetch and bin/fetch-product-images may know the image bank.');
    }

    /** @return list<string> */
    private function grep(string $path): array
    {
        $hits = [];
        $iterator = \is_dir($path)
            ? new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS))
            : new \ArrayIterator([new \SplFileInfo($path)]);
        foreach ($iterator as $file) {
            \assert($file instanceof \SplFileInfo);
            if ($file->isFile() && \stripos((string) \file_get_contents($file->getPathname()), 'openverse') !== false) {
                $hits[] = $file->getPathname();
            }
        }

        return $hits;
    }

    private function manifest(): ImageManifest
    {
        return ImageManifest::load($this->imagesDir . '/manifest.csv');
    }

    /** @return list<string> */
    private function imageFiles(): array
    {
        return \array_values(\array_filter(\glob($this->imagesDir . '/*') ?: [], static fn (string $path): bool => \basename($path) !== 'manifest.csv' && \basename($path) !== 'README.md'));
    }
}
