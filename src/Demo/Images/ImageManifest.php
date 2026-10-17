<?php

declare(strict_types=1);

namespace App\Demo\Images;

/**
 * The licence ledger of the demo product images (`fixtures/images/manifest.csv`): one row per SKU, so the licence of
 * every image shown by the demo can be audited. Read at runtime by the fixtures (local file only); written only by the
 * manual fetch script (bin/fetch-product-images).
 */
final class ImageManifest
{
    public const FIELDS = ['sku', 'file', 'source', 'creator', 'title', 'origin_url', 'license', 'license_url', 'fetched_at', 'scope'];

    /** @param array<string, array<string, string>> $rows by SKU */
    private function __construct(
        private readonly string $path,
        private array $rows,
    ) {
    }

    /** A missing file is an empty manifest: the first run of the fetch script creates it. */
    public static function load(string $path): self
    {
        $rows = [];
        $handle = \is_file($path) ? \fopen($path, 'r') : false;
        if ($handle !== false) {
            $header = self::strings(\fgetcsv($handle, null, ',', '"', ''));
            while (($line = \fgetcsv($handle, null, ',', '"', '')) !== false) {
                if (\count($line) === \count($header)) {
                    $row = \array_combine($header, self::strings($line));
                    $rows[$row['sku'] ?? ''] = $row;
                }
            }
            \fclose($handle);
        }

        return new self($path, $rows);
    }

    /**
     * @param array<array-key, string|null>|false $cells
     *
     * @return list<string>
     */
    private static function strings(array|false $cells): array
    {
        return \array_map(static fn (?string $cell): string => (string) $cell, \array_values($cells ?: []));
    }

    /** @return array<string, string>|null */
    public function row(string $sku): ?array
    {
        return $this->rows[$sku] ?? null;
    }

    public function fileFor(string $sku): ?string
    {
        return $this->rows[$sku]['file'] ?? null;
    }

    /** @return list<string> */
    public function skus(): array
    {
        return \array_keys($this->rows);
    }

    /** @return list<string> the distinct image files the manifest points to */
    public function files(): array
    {
        return \array_values(\array_unique(\array_column($this->rows, 'file')));
    }

    /** @return list<array<string, string>> */
    public function rows(): array
    {
        return \array_values($this->rows);
    }

    /** @param array<string, string> $row */
    public function add(array $row): void
    {
        $this->rows[$row['sku']] = $row;
        $this->save();
    }

    private function save(): void
    {
        $handle = \fopen($this->path, 'w');
        if ($handle === false) {
            throw new \RuntimeException(\sprintf('Cannot write the manifest "%s".', $this->path));
        }
        \fputcsv($handle, self::FIELDS, ',', '"', '');
        foreach ($this->rows as $row) {
            \fputcsv($handle, \array_map(static fn (string $field): string => $row[$field] ?? '', self::FIELDS), ',', '"', '');
        }
        \fclose($handle);
    }
}
