<?php

declare(strict_types=1);

namespace App\Tests\Unit\Architecture;

use App\Tests\Support\DemoCatalogs;
use App\Tests\Support\HardcodedPluginCountGuard;
use PHPUnit\Framework\TestCase;

/**
 * T-TECH-27-02: the number of plugins of the demo (27 after US-190) was typed in eight assertions; each new plugin broke the CI
 * of the pull request that added it (sprints 25, 26 and 27). The expected count comes from `config/demo/plugins.yaml`
 * (App\Tests\Support\DemoCatalogs::pluginCount()), never from a literal.
 */
final class HardcodedPluginCountTest extends TestCase
{
    public function testNoTestTypesTheNumberOfPlugins(): void
    {
        self::assertSame([], HardcodedPluginCountGuard::offenders(\dirname(__DIR__, 2)), 'Use DemoCatalogs::pluginCount() instead of the literal.');
    }

    public function testTheCountIsReadFromTheCatalogueOfThePlugins(): void
    {
        self::assertSame(substr_count((string) file_get_contents(\dirname(__DIR__, 3) . '/config/demo/plugins.yaml'), "\n        - slug: "), DemoCatalogs::pluginCount());
    }

    public function testTheScanFindsALiteralOnAPluginAssertionButNotTheOtherNumbers(): void
    {
        $dir = sys_get_temp_dir() . '/hardcoded-count-' . bin2hex(random_bytes(4));
        mkdir($dir);
        file_put_contents($dir . '/BadTest.php', implode("\n", [
            '<?php',
            'self::assertCount(27, self::catalog()->all());',
            'self::assertSame(27, $catalog->count());',
            "self::assertCount(26, \$page->filter('tr[data-plugin]'));",
            'self::assertCount(2, $plugins);',
            'self::assertCount(DemoCatalogs::pluginCount(), $plugins);',
            'self::assertSame(216, $calculator->unitsPerLayer(LogisticUnit::fromCatalog(0.35, 8.0)));',
            "self::assertSame('2026-10-23', \$plugin->checkedAt);",
            '',
        ]));

        try {
            $found = HardcodedPluginCountGuard::offenders($dir);
        } finally {
            unlink($dir . '/BadTest.php');
            rmdir($dir);
        }

        self::assertSame(['BadTest.php:2', 'BadTest.php:3', 'BadTest.php:4'], $found);
    }
}
