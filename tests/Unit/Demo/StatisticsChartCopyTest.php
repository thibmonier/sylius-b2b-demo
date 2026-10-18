<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo;

use PHPUnit\Framework\TestCase;

/**
 * US-087: assets/admin/scripts/statistics_chart.js is a copy of the Sylius admin script, only the series names
 * differ. It must follow the core: this fails when Sylius changes its version (re-copy it, then re-apply the two names).
 */
final class StatisticsChartCopyTest extends TestCase
{
    public function testTheCopyDiffersFromTheCoreScriptOnlyByTheSeriesNames(): void
    {
        $root = \dirname(__DIR__, 3);
        $core = $root . '/vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/Resources/assets/scripts/statistics_chart.js';
        self::assertFileExists($core);

        self::assertSame(self::normalize((string) file_get_contents($core)), self::normalize((string) file_get_contents($root . '/assets/admin/scripts/statistics_chart.js')));
    }

    private static function normalize(string $script): string
    {
        $lines = array_filter(
            explode("\n", $script),
            static fn (string $line): bool => 1 !== preg_match('/^\s*name:\s|^\/\/ Copy of /', $line),
        );

        return implode("\n", $lines);
    }
}
