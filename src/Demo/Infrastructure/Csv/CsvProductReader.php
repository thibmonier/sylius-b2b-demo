<?php

declare(strict_types=1);

namespace App\Demo\Infrastructure\Csv;

final class CsvProductReader
{
    /** @var list<string> */
    private const REQUIRED_COLUMNS = ['sku', 'name_fr', 'name_en', 'taxon', 'price_eur_ht'];

    private const UTF8_BOM = "\xEF\xBB\xBF";

    /** @var list<string> byte order marks of UTF-16 and UTF-32: not a file the fixtures can read */
    private const OTHER_BOMS = ["\xFF\xFE", "\xFE\xFF"];

    /**
     * Reads a CSV file and returns its rows as associative arrays.
     *
     * Validations, each stopping the import with the file and the line (a malformed file never loads half a catalogue):
     *  1. A UTF-8 BOM is ignored, the header names are trimmed, every required column must be present.
     *  2. Every cell is valid UTF-8.
     *  3. Every row has as many cells as the header.
     *  4. `attributes_json`, when filled, is a JSON object of strings.
     *  5. No two rows may share the same SKU within the file.
     *
     * @return list<array<string, string>>
     *
     * @throws CsvImportException     when the file is malformed
     * @throws \RuntimeException      when the file cannot be opened
     */
    public function read(string $filePath): array
    {
        return \array_column($this->readLines($filePath), 'row');
    }

    /**
     * Reads several files and checks that no SKU is used by two of them.
     *
     * @param list<string> $filePaths
     *
     * @return array<string, list<array<string, string>>> the rows of every file, by file path
     *
     * @throws CsvImportException when a file is malformed or a SKU is in two files
     */
    public function readAll(array $filePaths): array
    {
        /** @var array<string, string> $skuToPosition map SKU → "file:line" of its first occurrence */
        $skuToPosition = [];
        $all = [];
        foreach ($filePaths as $filePath) {
            foreach ($this->readLines($filePath) as ['line' => $line, 'row' => $row]) {
                $sku = \trim($row['sku'] ?? '');
                $position = $filePath . ':' . $line;
                if ($sku !== '' && isset($skuToPosition[$sku])) {
                    throw CsvImportException::duplicateSkuAcrossFiles($sku, $skuToPosition[$sku], $position);
                }
                $skuToPosition[$sku] = $position;
                $all[$filePath][] = $row;
            }
            $all[$filePath] ??= [];
        }

        return $all;
    }

    /** @return list<array{line: int, row: array<string, string>}> */
    private function readLines(string $filePath): array
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
     * @return list<array{line: int, row: array<string, string>}>
     */
    private function parseHandle($handle, string $filePath): array
    {
        $this->skipByteOrderMark($handle, $filePath);

        return $this->parseRows($handle, $this->readHeader($handle, $filePath), $filePath);
    }

    /**
     * @param resource $handle
     *
     * @return list<string>
     */
    private function readHeader($handle, string $filePath): array
    {
        $headerRaw = \fgetcsv($handle, null, ',', '"', '');
        if ($headerRaw === false) {
            throw new \RuntimeException(\sprintf('CSV file "%s" is empty.', $filePath));
        }
        $this->assertUtf8($headerRaw, $filePath, 1);

        // Null cells (e.g. trailing commas in the header row) are normalised to
        // empty string so that $header is list<string> and safe as array_combine keys.
        // Required-column validation below catches any genuinely missing columns.
        $header = \array_map(static fn (string|null $h): string => \trim($h ?? ''), $headerRaw);

        foreach (self::REQUIRED_COLUMNS as $column) {
            if (!\in_array($column, $header, true)) {
                throw CsvImportException::missingRequiredColumn($column, $filePath);
            }
        }

        return $header;
    }

    /**
     * @param resource     $handle
     * @param list<string> $header
     *
     * @return list<array{line: int, row: array<string, string>}>
     */
    private function parseRows($handle, array $header, string $filePath): array
    {
        /** @var array<string, int> $skuToLine map SKU → first line number it was seen on */
        $skuToLine = [];
        /** @var list<array{line: int, row: array<string, string>}> $rows */
        $rows = [];
        $lineNumber = 1; // line 1 = header

        while (($rawRow = \fgetcsv($handle, null, ',', '"', '')) !== false) {
            ++$lineNumber;

            // Skip blank lines: fgetcsv returns a single-element array with null or empty string
            if (\count($rawRow) === 1 && ($rawRow[0] === null || $rawRow[0] === '')) {
                continue;
            }

            $this->assertUtf8($rawRow, $filePath, $lineNumber);
            if (\count($rawRow) !== \count($header)) {
                throw CsvImportException::malformedRow($filePath, $lineNumber, \count($header), \count($rawRow));
            }

            /** @var array<string, string> $row */
            $row = \array_combine($header, $rawRow);
            $this->assertAttributes($row['attributes_json'] ?? '', $filePath, $lineNumber);

            $sku = \trim($row['sku'] ?? '');
            if ($sku !== '') {
                if (isset($skuToLine[$sku])) {
                    throw CsvImportException::duplicateSku($sku, $skuToLine[$sku], $lineNumber, $filePath);
                }
                $skuToLine[$sku] = $lineNumber;
            }

            $rows[] = ['line' => $lineNumber, 'row' => $row];
        }

        return $rows;
    }

    /**
     * A UTF-8 BOM is skipped before the CSV parser sees it (it would glue itself to a quoted first cell); the BOMs of
     * other encodings are refused.
     *
     * @param resource $handle
     */
    private function skipByteOrderMark($handle, string $filePath): void
    {
        $start = (string) \fread($handle, 3);
        if (\str_starts_with($start, self::UTF8_BOM)) {
            return;
        }
        foreach (self::OTHER_BOMS as $bom) {
            if (\str_starts_with($start, $bom)) {
                throw CsvImportException::invalidEncoding($filePath, 1);
            }
        }
        \rewind($handle);
    }

    /** @param array<array-key, string|null> $cells */
    private function assertUtf8(array $cells, string $filePath, int $line): void
    {
        foreach ($cells as $cell) {
            if ($cell !== null && !\mb_check_encoding($cell, 'UTF-8')) {
                throw CsvImportException::invalidEncoding($filePath, $line);
            }
        }
    }

    private function assertAttributes(string $json, string $filePath, int $line): void
    {
        $json = \trim($json);
        if ($json === '') {
            return;
        }

        try {
            $attributes = \json_decode($json, true, 8, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw CsvImportException::invalidAttributes($filePath, $line, $e->getMessage());
        }
        if (!\is_array($attributes) || ($attributes !== [] && \array_is_list($attributes))) {
            throw CsvImportException::invalidAttributes($filePath, $line, 'a JSON object of attribute code => text is expected');
        }
        foreach ($attributes as $code => $value) {
            if (!\is_string($value)) {
                throw CsvImportException::invalidAttributes($filePath, $line, \sprintf('the value of "%s" must be a string, %s given', $code, \get_debug_type($value)));
            }
        }
    }
}
