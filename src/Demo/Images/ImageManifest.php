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
    public const FIELDS = ['sku', 'file', 'source', 'creator', 'title', 'origin_url', 'license', 'license_url', 'fetched_at', 'scope', 'status'];

    /**
     * US-197: the point focal of the photo, "x y" in percent of the 4:3 file (`object-position` of the card and of the product page).
     * Empty or absent: the default framing (centered). Not required, so it is not part of FIELDS.
     */
    public const FOCAL = 'focal';

    private const FOCAL_PATTERN = '/^(\d{1,3}(?:\.\d+)?) (\d{1,3}(?:\.\d+)?)$/';

    /** US-092: a person looked at the image and judged it fit for the products that show it. */
    public const STATUS_VALIDATED = 'validated';

    /** US-092: no fitting photo: a neutral visual of the category, drawn for the demo, replaces it. */
    public const STATUS_FALLBACK = 'fallback';

    /** What the fetch script writes: not shown as fit until someone reviews it (ImageManifestAuditor refuses it). */
    public const STATUS_UNREVIEWED = 'unreviewed';

    public const SOURCE_GENERATED = 'generated';

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
            for ($number = 2; ($line = \fgetcsv($handle, null, ',', '"', '')) !== false; ++$number) {
                if ($line === [null]) {
                    continue;
                }
                if (\count($line) !== \count($header)) {
                    throw new \RuntimeException(\sprintf('Malformed manifest row at line %d of "%s".', $number, $path));
                }
                $row = \array_combine($header, self::strings($line));
                $rows[$row['sku'] ?? ''] = $row;
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

    /** @return array{float, float}|null x and y in percent, null when the photo has no (valid) point focal */
    public function focalFor(string $sku): ?array
    {
        $focal = $this->rows[$sku][self::FOCAL] ?? '';
        if (!self::isValidFocal($focal)) {
            return null;
        }
        [$x, $y] = \explode(' ', $focal);

        return [(float) $x, (float) $y];
    }

    /** Well formed: two percentages between 0 and 100 (the audit refuses the others and names the SKU). */
    public static function isValidFocal(string $focal): bool
    {
        return \preg_match(self::FOCAL_PATTERN, $focal, $matches) === 1 && (float) $matches[1] <= 100.0 && (float) $matches[2] <= 100.0;
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
        $columns = [...self::FIELDS, self::FOCAL];
        \fputcsv($handle, $columns, ',', '"', '');
        foreach ($this->rows as $row) {
            \fputcsv($handle, \array_map(static fn (string $field): string => $row[$field] ?? '', $columns), ',', '"', '');
        }
        \fclose($handle);
    }
}
