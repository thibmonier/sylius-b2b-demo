<?php

declare(strict_types=1);

namespace App\Demo\Logistics;

/**
 * Units of a product filling one container, as a simple documented estimate (the plugin presets are indicative):
 *
 *   capacity = min( floor(usable volume x efficiency / unit volume), floor(payload / unit weight) )
 *
 * A product without weight or dimension, or a container without volume or payload, has no capacity (0).
 */
final class ContainerCapacityCalculator
{
    public function capacity(LogisticUnit $unit, ContainerSpace $space): int
    {
        if (!$unit->isUsable() || $space->payloadG <= 0 || $space->usableVolumeMm3() <= 0) {
            return 0;
        }

        return \min(
            \intdiv($space->usableVolumeMm3(), $unit->volumeMm3()),
            \intdiv($space->payloadG, $unit->weightG),
        );
    }

    /** Units side by side in one layer: usable floor / footprint of the product. */
    public function unitsPerLayer(LogisticUnit $unit, ContainerSpace $space): int
    {
        if (!$unit->isUsable() || $space->usableFloorMm2() <= 0) {
            return 0;
        }

        return \intdiv($space->usableFloorMm2(), $unit->footprintMm2());
    }
}
