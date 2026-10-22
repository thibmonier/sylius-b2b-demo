<?php

declare(strict_types=1);

namespace App\Demo\Order;

final readonly class DemoOrderSpec
{
    /** Delivery site other than the default one (Lyon). */
    public const SITE_SAINT_PRIEST = 'saint-priest';

    /** @param array<string, positive-int> $items quantity by variant code */
    public function __construct(
        public string $customerEmail,
        public int $daysAgo,
        public array $items,
        public string $poReference,
        public OrderScenario $scenario,
        public ?string $costCenter = null,
        public ?string $approverEmail = null,
        public ?string $paymentMethod = null,
        public ?string $deliverySite = null,
    ) {
    }
}
