<?php

declare(strict_types=1);

namespace App\Demo\Erp;

/** A batch of prices or stock imported from a file, as the journal of the export plugin remembers it. */
final readonly class ImportedBatchRow
{
    /** @param list<array{line: int, reason: string}> $rejections */
    public function __construct(
        public string $name,
        public string $checksum,
        public string $kind,
        public string $outcome,
        public \DateTimeImmutable $at,
        public int $read,
        public int $applied,
        public int $rejected,
        public array $rejections,
    ) {
    }
}
