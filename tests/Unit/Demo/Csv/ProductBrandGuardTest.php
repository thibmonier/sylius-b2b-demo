<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Csv;

use PHPUnit\Framework\TestCase;

/**
 * US-195: the catalogue of the demo names no real brand, in the fixtures or anywhere else in the repository.
 * The only place that spells the brands out is config/demo/forbidden-brands.txt.
 */
final class ProductBrandGuardTest extends TestCase
{
    private const COLUMNS = ['name_fr', 'name_en', 'description_fr', 'description_en', 'mpn'];

    private const SCANNED_DIRECTORIES = ['src', 'templates', 'translations', 'config', 'docs', 'tests', 'assets', 'bin'];

    private const SCANNED_EXTENSIONS = ['php', 'twig', 'yaml', 'yml', 'json', 'md', 'js', 'scss', 'sh', 'csv'];

    private string $root;

    protected function setUp(): void
    {
        $this->root = \dirname(__DIR__, 4);
    }

    public function testNoCatalogueFixtureNamesARealBrand(): void
    {
        $violations = [];
        foreach ($this->csvFiles() as $file) {
            foreach ($this->rows($file) as $line => $values) {
                foreach (self::COLUMNS as $column) {
                    $brand = $this->brandIn($values[$column] ?? '');
                    if ($brand !== null) {
                        $violations[] = \sprintf('%s:%d %s names the brand "%s"', \basename($file), $line, $column, $brand);
                    }
                }
            }
        }

        self::assertSame([], $violations, "A real brand is back in a fixture:\n" . \implode("\n", $violations));
    }

    public function testNoOtherFileOfTheRepositoryNamesARealBrand(): void
    {
        $violations = [];
        foreach (self::SCANNED_DIRECTORIES as $directory) {
            foreach ($this->files($this->root . '/' . $directory) as $file) {
                foreach (\file($file, \FILE_IGNORE_NEW_LINES) ?: [] as $number => $text) {
                    $brand = $this->brandIn($text);
                    if ($brand !== null) {
                        $violations[] = \sprintf('%s:%d names the brand "%s"', \substr($file, \strlen($this->root) + 1), $number + 1, $brand);
                    }
                }
            }
        }

        self::assertSame([], $violations, "A real brand is cited:\n" . \implode("\n", $violations));
    }

    public function testProductNamesAreDistinctWithinAFamily(): void
    {
        $seen = [];
        $duplicates = [];
        foreach ($this->csvFiles() as $file) {
            foreach ($this->rows($file) as $row) {
                $family = \substr($row['sku'], 0, (int) \strrpos($row['sku'], '-'));
                foreach (['name_fr', 'name_en'] as $column) {
                    $key = $column . '|' . $family . '|' . \mb_strtolower($row[$column]);
                    if (isset($seen[$key])) {
                        $duplicates[] = \sprintf('%s and %s share the %s "%s"', $seen[$key], $row['sku'], $column, $row[$column]);
                    }
                    $seen[$key] = $row['sku'];
                }
            }
        }

        self::assertSame([], $duplicates, \implode("\n", $duplicates));
    }

    public function testTheProductCodeAndTheSlugOfTheFirstHardHatAreUnchanged(): void
    {
        $skus = \array_column($this->rows($this->root . '/fixtures/csv/epi.csv'), 'sku');

        self::assertContains('EPI-CASK-001', $skus);
        self::assertSame('epi-cask-001', \strtolower(\str_replace(['_', ' '], '-', 'EPI-CASK-001')));
        self::assertCount(210, \array_merge(...\array_map(fn (string $file): array => $this->rows($file), $this->csvFiles())));
    }

    public function testTheBlacklistIsReadAndMatchesWholeWordsAsWrittenOrInUpperCase(): void
    {
        $brand = $this->brands()[1];

        self::assertSame($brand, $this->brandIn('Chaussure ' . $brand . ' noire'));
        self::assertSame($brand, $this->brandIn('chaussure ' . \strtoupper($brand) . '-001'));
        self::assertNull($this->brandIn('Profil 3m de long'));
        self::assertNull($this->brandIn('Casque de chantier blanc'));
    }

    /** @return list<string> */
    private function brands(): array
    {
        $lines = \file($this->root . '/config/demo/forbidden-brands.txt', \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES) ?: [];

        return \array_values(\array_filter(\array_map('trim', $lines), static fn (string $line): bool => $line !== '' && $line[0] !== '#'));
    }

    private function brandIn(string $text): ?string
    {
        foreach ($this->brands() as $brand) {
            foreach (\array_unique([$brand, \strtoupper($brand)]) as $spelling) {
                if (\preg_match('/(?<![\p{L}\p{N}])' . \preg_quote($spelling, '/') . '(?![\p{L}\p{N}])/u', $text) === 1) {
                    return $brand;
                }
            }
        }

        return null;
    }

    /** @return list<string> */
    private function csvFiles(): array
    {
        return \glob($this->root . '/fixtures/csv/*.csv') ?: [];
    }

    /** @return array<int, array<string, string>> the rows by line number of the file */
    private function rows(string $file): array
    {
        $handle = \fopen($file, 'r') ?: throw new \RuntimeException($file);
        /** @var list<string> $header */
        $header = \fgetcsv($handle, escape: '\\') ?: [];
        $rows = [];
        for ($line = 2; ($row = \fgetcsv($handle, escape: '\\')) !== false; ++$line) {
            $rows[$line] = \array_combine($header, \array_map(strval(...), $row));
        }
        \fclose($handle);

        return $rows;
    }

    /** @return iterable<string> */
    private function files(string $directory): iterable
    {
        $list = $this->root . '/config/demo/forbidden-brands.txt';
        $this_ = __FILE__;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            \assert($file instanceof \SplFileInfo);
            $path = $file->getPathname();
            if ($path === $list || $path === $this_ || !\in_array($file->getExtension(), self::SCANNED_EXTENSIONS, true)) {
                continue;
            }
            yield $path;
        }
    }
}
