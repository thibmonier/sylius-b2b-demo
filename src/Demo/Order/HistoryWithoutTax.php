<?php

declare(strict_types=1);

namespace App\Demo\Order;

/**
 * US-202: the order history of the demo stays without VAT (the amounts of the purchasing dashboard do not change silently), while the
 * products now carry a tax category. The factory of the history switches this on around the creation of an order; the tax processor
 * reads it and leaves the order alone (which also spares the tax lookups of every line).
 */
final class HistoryWithoutTax
{
    private bool $active = false;

    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @template T
     *
     * @param callable(): T $build
     *
     * @return T
     */
    public function during(callable $build): mixed
    {
        $this->active = true;

        try {
            return $build();
        } finally {
            $this->active = false;
        }
    }
}
