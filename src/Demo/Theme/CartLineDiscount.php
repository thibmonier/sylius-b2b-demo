<?php

declare(strict_types=1);

namespace App\Demo\Theme;

/** What a volume tier or the company price takes off one line of the cart, in minor units (positive amounts). */
final readonly class CartLineDiscount
{
    public function __construct(
        public int $catalogueUnitPrice,
        public int $amount,
        public float $percent,
        public bool $negotiated,
    ) {
    }
}
