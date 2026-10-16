<?php

declare(strict_types=1);

namespace App\Demo\Infrastructure\Csv;

final class CsvImportException extends \RuntimeException
{
    public static function missingRequiredColumn(string $column, string $filePath): self
    {
        return new self(\sprintf(
            'Missing required column "%s" in CSV file "%s". Import aborted — no product has been created.',
            $column,
            $filePath,
        ));
    }

    public static function duplicateSku(string $sku, int $firstLine, int $secondLine, string $filePath): self
    {
        return new self(\sprintf(
            'Duplicate SKU "%s" in CSV file "%s": first occurrence at line %d, conflict at line %d. Import aborted — no product has been created.',
            $sku,
            $filePath,
            $firstLine,
            $secondLine,
        ));
    }

    public static function duplicateSkuAcrossFiles(string $sku, string $firstPosition, string $secondPosition): self
    {
        return new self(\sprintf(
            'Duplicate SKU "%s" across CSV files: first at %s, conflict at %s. Import aborted — no product has been created.',
            $sku,
            $firstPosition,
            $secondPosition,
        ));
    }

    public static function malformedRow(string $filePath, int $line, int $expected, int $found): self
    {
        return new self(\sprintf(
            'Malformed row at %s:%d: %d cells expected (the header), %d found. Import aborted — no product has been created.',
            $filePath,
            $line,
            $expected,
            $found,
        ));
    }

    public static function invalidAttributes(string $filePath, int $line, string $reason): self
    {
        return new self(\sprintf(
            'Invalid attributes_json at %s:%d: %s. Import aborted — no product has been created.',
            $filePath,
            $line,
            $reason,
        ));
    }

    public static function invalidEncoding(string $filePath, int $line): self
    {
        return new self(\sprintf(
            'Invalid encoding at %s:%d: the file must be UTF-8 (a BOM is accepted). Import aborted — no product has been created.',
            $filePath,
            $line,
        ));
    }
}
