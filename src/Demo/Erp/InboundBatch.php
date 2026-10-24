<?php

declare(strict_types=1);

namespace App\Demo\Erp;

/**
 * What "ERP Démo (fictif)" answered to a file of orders: an acknowledgement (its name) or a rejection (no name, a reason).
 * Simulated: the figures describe the checks of the simulator, not an accounting.
 */
final readonly class InboundBatch
{
    public function __construct(
        public string $source,
        public string $name,
        public \DateTimeImmutable $at,
        public int $read,
        public int $accepted,
        public int $rejected,
        public ?string $reason,
    ) {
    }
}
