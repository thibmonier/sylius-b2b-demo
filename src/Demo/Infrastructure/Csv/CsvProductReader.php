<?php

declare(strict_types=1);

namespace App\Demo\Infrastructure\Csv;

final class CsvProductReader
{
    /** @var list<string> */
    private const REQUIRED_COLUMNS = ['sku', 'name_fr', 'name_en', 'taxon', 'price_eur_ht'];

    /**
     * Reads a CSV file and returns its rows as associative arrays.
     *
     * Performs two validations before returning data:
     *  1. Every required column must be present in the header row.
     *  2. No two rows may share the same SKU within the file.
     *
     * @return list<array<string, string>>
     *
     * @throws CsvImportException     when a required column is missing or a SKU is duplicated
     * @throws \RuntimeException      when the file cannot be opened
     */
    public function read(string $filePath): array
    {
        $handle = \fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException(\sprintf('Cannot open CSV file "%s".', $filePath));
        }

        try {
            return $this->parseHandle($handle, $filePath);
        } finally {
            \fclose($handle);
        }
    }

    /**
     * @param resource $handle
     *
     * @return list<array<string, string>>
     */
    private function parseHandle($handle, string $filePath): array
    {
        $headerRaw = \fgetcsv($handle);
        if ($headerRaw === false) {
            throw new \RuntimeException(\sprintf('CSV file "%s" is empty.', $filePath));
        }

        // Null cells (e.g. trailing commas in the header row) are normalised to
        // empty string so that $header is list<string> and safe as array_combine keys.
        // Required-column validation below catches any genuinely missing columns.
        $header = \array_map(static fn (string|null $h): string => $h ?? '', $headerRaw);

        foreach (self::REQUIRED_COLUMNS as $column) {
            if (!\in_array($column, $header, true)) {
                throw CsvImportException::missingRequiredColumn($column, $filePath);
            }
        }

        /** @var array<string, int> $skuToLine map SKU → first line number it was seen on */
        $skuToLine = [];
        /** @var list<array<string, string>> $rows */
        $rows = [];
        $lineNumber = 1; // line 1 = header

        while (($rawRow = \fgetcsv($handle)) !== false) {
            ++$lineNumber;

            // Skip blank lines: fgetcsv returns a single-element array with null or empty string
            if (\count($rawRow) === 1 && ($rawRow[0] === null || $rawRow[0] === '')) {
                continue;
            }

            if (\count($rawRow) !== \count($header)) {
                // Skip malformed rows silently (column count mismatch)
                continue;
            }

            /** @var array<string, string> $row */
            $row = \array_combine($header, $rawRow);

            $sku = \trim($row['sku'] ?? '');
            if ($sku !== '') {
                if (isset($skuToLine[$sku])) {
                    throw CsvImportException::duplicateSku($sku, $skuToLine[$sku], $lineNumber, $filePath);
                }
                $skuToLine[$sku] = $lineNumber;
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
