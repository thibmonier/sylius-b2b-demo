<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Demo\Theme\StockStatus;

/**
 * The fictitious stock of a variant (US-198, T-198-12), a pure function of its SKU: no random draw, so every reset of the demo
 * gives the same shelves. About 5 % of the catalogue is out of stock, about 10 % is low (below LOW_THRESHOLD), the rest is available.
 */
final class DemoStockLevel
{
    public const LOW_THRESHOLD = StockStatus::LOW_THRESHOLD;

    private const OUT_OF_STOCK_PERCENT = 5;

    private const LOW_PERCENT = 10;

    private const AVAILABLE_MIN = 25;

    private const AVAILABLE_SPREAD = 476;

    public static function forSku(string $sku): int
    {
        $hash = crc32($sku);
        $bucket = $hash % 100;
        $spread = intdiv($hash, 100);
        if ($bucket < self::OUT_OF_STOCK_PERCENT) {
            return 0;
        }
        if ($bucket < self::OUT_OF_STOCK_PERCENT + self::LOW_PERCENT) {
            return 1 + $spread % (self::LOW_THRESHOLD - 1);
        }

        return self::AVAILABLE_MIN + $spread % self::AVAILABLE_SPREAD;
    }
}
