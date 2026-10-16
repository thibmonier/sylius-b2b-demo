<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

/** The cart sent back by the shop (PunchOutOrderMessage), as displayed by the simulator. */
final readonly class ReceivedCart
{
    /** @param list<ReceivedLine> $lines */
    public function __construct(
        public string $buyerCookie,
        public string $currency,
        public string $total,
        public array $lines,
    ) {
    }

    public function isEmpty(): bool
    {
        return [] === $this->lines;
    }
}
