<?php

declare(strict_types=1);

namespace App\Demo\Erp;

use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** Reads the journal of the import of the export plugin (one line per batch of prices or stock): read only. */
final readonly class DbalImportedBatches implements ImportedBatches
{
    public function __construct(
        #[Autowire(service: 'doctrine.dbal.default_connection')]
        private Connection $connection,
    ) {
    }

    public function all(): array
    {
        $rows = $this->connection->fetchAllAssociative('SELECT name, checksum, kind, outcome, lines_read, lines_applied, lines_rejected, report, imported_at FROM tmonier_erp_flatfile_import_batch ORDER BY imported_at, name');

        $batches = [];
        foreach ($rows as $row) {
            $rejections = [];
            foreach ((array) json_decode(self::text($row['report'] ?? '[]'), true) as $line) {
                if (\is_array($line)) {
                    $rejections[] = ['line' => self::number($line['line'] ?? 0), 'reason' => self::text($line['reason'] ?? '')];
                }
            }
            $batches[] = new ImportedBatchRow(
                self::text($row['name'] ?? ''),
                self::text($row['checksum'] ?? ''),
                self::text($row['kind'] ?? ''),
                self::text($row['outcome'] ?? ''),
                new \DateTimeImmutable(self::text($row['imported_at'] ?? '') ?: 'now', new \DateTimeZone('UTC')),
                self::number($row['lines_read'] ?? 0),
                self::number($row['lines_applied'] ?? 0),
                self::number($row['lines_rejected'] ?? 0),
                $rejections,
            );
        }

        return $batches;
    }

    private static function text(mixed $value): string
    {
        return \is_string($value) ? $value : '';
    }

    private static function number(mixed $value): int
    {
        return \is_numeric($value) ? (int) $value : 0;
    }
}
