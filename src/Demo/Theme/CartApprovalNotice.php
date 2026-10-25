<?php

declare(strict_types=1);

namespace App\Demo\Theme;

/** What the buyer is told before validating: the order will wait for the approval of the company (US-200). */
final readonly class CartApprovalNotice
{
    public function __construct(
        public ?int $orderLimit,
        public int $total,
        public string $currencyCode,
        public ?string $company,
        public ?string $approver,
    ) {
    }
}
