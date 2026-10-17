<?php

declare(strict_types=1);

namespace App\Demo\Logistics;

/** Usable space of a container type (millimetres, grams): the loading height of a pallet, the inner height of a carton. */
final readonly class ContainerSpace
{
    public function __construct(
        public int $lengthMm,
        public int $widthMm,
        public int $heightMm,
        public int $payloadG,
        public int $efficiencyPercent,
    ) {
    }

    public function usableVolumeMm3(): int
    {
        return \intdiv($this->lengthMm * $this->widthMm * $this->heightMm * $this->efficiencyPercent, 100);
    }

    public function usableFloorMm2(): int
    {
        return \intdiv($this->lengthMm * $this->widthMm * $this->efficiencyPercent, 100);
    }
}
