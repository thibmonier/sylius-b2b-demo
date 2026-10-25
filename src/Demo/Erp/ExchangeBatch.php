<?php

declare(strict_types=1);

namespace App\Demo\Erp;

/** One line of the page "ERP exchanges": a file sent to "ERP Démo (fictif)" (`out`) or what it answered (`in`). Figures simulated for `in`. */
final readonly class ExchangeBatch
{
    /** @param list<array{line: int, reason: string}> $report the rejected lines of an import (line 0 stands for the whole file) */
    public function __construct(
        public string $direction,
        public string $name,
        public string $source,
        public \DateTimeImmutable $at,
        public ?int $read,
        public ?int $accepted,
        public ?int $rejected,
        public ?string $reason,
        public ?string $kind = null,
        public ?string $checksum = null,
        public ?string $outcome = null,
        public array $report = [],
    ) {
    }
}
