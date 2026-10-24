<?php

declare(strict_types=1);

namespace App\Demo\Erp;

final readonly class SimulationResult
{
    public function __construct(
        public SimulationStatus $status,
        public ?string $source = null,
        public int $read = 0,
        public int $accepted = 0,
        public int $rejected = 0,
    ) {
    }
}
