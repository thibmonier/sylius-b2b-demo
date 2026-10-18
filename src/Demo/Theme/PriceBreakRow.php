<?php

declare(strict_types=1);

namespace App\Demo\Theme;

/** One price break shown to the customer: `negotiated` when their company price beats the public one. */
final readonly class PriceBreakRow
{
    public function __construct(
        public int $minQuantity,
        public int $unitPrice,
        public float $percentage,
        public bool $negotiated,
    ) {
    }
}
