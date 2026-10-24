<?php

declare(strict_types=1);

namespace App\Demo\Erp;

use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** Reads the journal of the export plugin (one line per exported event, the file it went to): read only. */
final readonly class DbalOutboundBatches implements OutboundBatches
{
    public function __construct(
        #[Autowire(service: 'doctrine.dbal.default_connection')]
        private Connection $connection,
    ) {
    }

    public function all(): array
    {
        $rows = $this->connection->fetchAllAssociative('SELECT file_name, MIN(exported_at) AS exported_at, COUNT(*) AS orders FROM tmonier_erp_flatfile_exported_event GROUP BY file_name');

        $batches = [];
        foreach ($rows as $row) {
            $batches[] = new OutboundBatch(self::text($row['file_name'] ?? null), new \DateTimeImmutable(self::text($row['exported_at'] ?? null) ?: 'now', new \DateTimeZone('UTC')), \is_numeric($row['orders'] ?? null) ? (int) $row['orders'] : 0);
        }

        return $batches;
    }

    private static function text(mixed $value): string
    {
        return \is_string($value) ? $value : '';
    }
}
