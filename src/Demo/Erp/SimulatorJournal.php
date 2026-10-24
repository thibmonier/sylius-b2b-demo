<?php

declare(strict_types=1);

namespace App\Demo\Erp;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The journal of the simulator: one JSON line per file of orders it processed, in a hidden file of the exchange directory, so that the
 * reset of the demo (which empties the directory) empties it too. It never creates the directory.
 */
final readonly class SimulatorJournal
{
    private const FILE = '.erp-demo-journal.jsonl';

    public function __construct(
        #[Autowire(param: 'tmonier_erp_flatfile.exchange_directory')]
        private string $directory,
    ) {
    }

    public function record(InboundBatch $batch): void
    {
        if (!is_dir($this->directory)) {
            return;
        }

        file_put_contents($this->path(), json_encode([
            'source' => $batch->source,
            'name' => $batch->name,
            'at' => $batch->at->format(\DATE_ATOM),
            'read' => $batch->read,
            'accepted' => $batch->accepted,
            'rejected' => $batch->rejected,
            'reason' => $batch->reason,
        ], \JSON_THROW_ON_ERROR) . "\n", \FILE_APPEND | \LOCK_EX);
    }

    /** @return list<InboundBatch> the oldest first */
    public function all(): array
    {
        $content = is_file($this->path()) ? @file_get_contents($this->path()) : false;
        if (false === $content) {
            return [];
        }

        $batches = [];
        foreach (explode("\n", $content) as $line) {
            $row = '' === trim($line) ? null : json_decode($line, true);
            if (!\is_array($row)) {
                continue;
            }
            $batches[] = new InboundBatch(
                self::text($row['source'] ?? null),
                self::text($row['name'] ?? null),
                new \DateTimeImmutable(self::text($row['at'] ?? null) ?: 'now'),
                self::number($row['read'] ?? null),
                self::number($row['accepted'] ?? null),
                self::number($row['rejected'] ?? null),
                \is_string($row['reason'] ?? null) ? $row['reason'] : null,
            );
        }

        return $batches;
    }

    public function processed(string $source): bool
    {
        foreach ($this->all() as $batch) {
            if ($batch->source === $source) {
                return true;
            }
        }

        return false;
    }

    public function clear(): void
    {
        if (is_file($this->path())) {
            unlink($this->path());
        }
    }

    private function path(): string
    {
        return rtrim($this->directory, '/') . '/' . self::FILE;
    }

    private static function text(mixed $value): string
    {
        return \is_string($value) ? $value : '';
    }

    private static function number(mixed $value): int
    {
        return \is_int($value) ? $value : 0;
    }
}
