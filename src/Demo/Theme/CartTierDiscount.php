<?php

declare(strict_types=1);

namespace App\Demo\Theme;

/** What the volume tiers and the company price take off a cart, in minor units (positive amounts). */
final readonly class CartTierDiscount
{
    public function __construct(
        public int $tierAmount,
        public int $negotiatedAmount,
    ) {
    }

    public function total(): int
    {
        return $this->tierAmount + $this->negotiatedAmount;
    }
}
