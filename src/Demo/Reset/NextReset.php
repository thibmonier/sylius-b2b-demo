<?php

declare(strict_types=1);

namespace App\Demo\Reset;

final readonly class NextReset
{
    /** @param bool $today whether the reset is on the current day in Paris (otherwise the next one) */
    public function __construct(public \DateTimeImmutable $at, public bool $today)
    {
    }
}
