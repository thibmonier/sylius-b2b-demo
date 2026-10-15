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
}
