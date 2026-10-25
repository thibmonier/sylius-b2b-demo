<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\DataFixtures;

use App\Demo\DataFixtures\DemoStockLevel;
use PHPUnit\Framework\TestCase;

/** US-198 (T-198-12): the fictitious stock of the catalogue is a pure function of the SKU, so every reset gives the same shelves. */
final class DemoStockLevelTest extends TestCase
{
    public function testTheSameSkuAlwaysGivesTheSameQuantity(): void
    {
        self::assertSame(DemoStockLevel::forSku('VISS-001'), DemoStockLevel::forSku('VISS-001'));
    }

    public function testAboutFivePercentOfTheCatalogueIsOutOfStockAndSomeStockIsLow(): void
    {
        $levels = [];
        foreach (['VISS', 'OUTI', 'FIXA', 'EPI-CASK', 'EMBA', 'SIGN', 'PAPE'] as $prefix) {
            for ($i = 1; $i <= 40; ++$i) {
                $levels[] = DemoStockLevel::forSku(\sprintf('%s-%03d', $prefix, $i));
            }
        }

        $out = \count(array_filter($levels, static fn (int $level): bool => 0 === $level));
        $low = \count(array_filter($levels, static fn (int $level): bool => $level > 0 && $level < DemoStockLevel::LOW_THRESHOLD));

        self::assertGreaterThanOrEqual(3, $out);
        self::assertLessThanOrEqual((int) (\count($levels) * 0.10), $out);
        self::assertGreaterThanOrEqual(5, $low);
        self::assertGreaterThanOrEqual(0, min($levels));
    }
}
