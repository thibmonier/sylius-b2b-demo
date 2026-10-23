<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof\Compatibility;

use App\Demo\Proof\Compatibility\CompatibilityReader;
use App\Demo\Proof\Refresh\PluginCatalogFile;
use PHPUnit\Framework\TestCase;

/**
 * US-171: the committed file (config/demo/compatibility.json, written by bin/compat-refresh) covers the catalogue, every
 * section is accepted by the reader, links point to plugins of the catalogue and it holds no secret.
 */
final class CompatibilityDataFileTest extends TestCase
{
    private const FILE = __DIR__ . '/../../../../../config/demo/compatibility.json';

    private const CATALOGUE = __DIR__ . '/../../../../../config/demo/plugins.yaml';

    public function testTheFileCoversTheCatalogueAndEverySectionIsAcceptedByTheReader(): void
    {
        $slugs = array_column(PluginCatalogFile::entries(self::CATALOGUE), 'slug');
        $report = new CompatibilityReader(self::FILE)->report();

        self::assertNotNull($report->generatedAt());
        self::assertNotNull($report->demo(), 'the stack of the demo is published');
        self::assertCount(25, $slugs);
        foreach ($slugs as $slug) {
            $plugin = $report->plugin($slug);
            self::assertTrue($plugin->hasDependencies(), $slug . ' dependencies would be dropped');
            self::assertTrue($plugin->hasCi(), $slug . ' CI matrix would be dropped');
            foreach (['php', 'sylius', 'symfony', 'database'] as $axis) {
                self::assertNotSame([], $plugin->ci[$axis] ?? [], $slug . ' ' . $axis);
            }
            self::assertSame([], array_diff([...$plugin->requires, ...$plugin->suggests], $slugs), $slug . ' links to a plugin outside the catalogue');
        }
    }

    public function testTradeCreditRequiresOrderHoldAndOnlySuggestsFacturx(): void
    {
        $plugin = new CompatibilityReader(self::FILE)->report()->plugin('sylius-trade-credit-plugin');

        self::assertContains('sylius-order-hold-plugin', $plugin->requires);
        self::assertNotContains('sylius-facturx-plugin', $plugin->requires);
        self::assertContains('sylius-facturx-plugin', $plugin->suggests);
    }

    public function testTheFileHoldsNoSecret(): void
    {
        $content = (string) file_get_contents(self::FILE);

        self::assertDoesNotMatchRegularExpression('/gh[pousr]_[A-Za-z0-9]{20,}|github_pat_|Bearer |eyJ[A-Za-z0-9_-]{10,}|access_token/', $content);
        self::assertStringNotContainsString('@', $content);
    }
}
