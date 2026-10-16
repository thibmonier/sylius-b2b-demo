<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Plugin;

use App\Demo\Plugin\DemoPluginCatalog;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/** US-014 — the plugin registry matches composer.json and rejects invalid entries by plugin name. */
final class DemoPluginCatalogTest extends TestCase
{
    public function testTheCatalogHoldsExactlyTheComposerPlugins(): void
    {
        $composer = json_decode((string) file_get_contents(\dirname(__DIR__, 4) . '/composer.json'), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($composer);
        self::assertIsArray($composer['require']);
        $expected = [];
        foreach (array_keys($composer['require']) as $package) {
            if (str_starts_with((string) $package, 'tmonier/')) {
                $expected[] = substr((string) $package, 8);
            }
        }
        sort($expected);

        $slugs = array_map(static fn ($plugin): string => $plugin->slug, self::catalog()->all());
        sort($slugs);

        self::assertCount(21, $slugs);
        self::assertSame($expected, $slugs);
    }

    public function testEveryPluginHasBothDescriptionsAndAKnownCategory(): void
    {
        foreach (self::catalog()->all() as $plugin) {
            self::assertNotSame('', $plugin->descriptionFr, $plugin->slug);
            self::assertNotSame('', $plugin->descriptionEn, $plugin->slug);
            self::assertNotSame($plugin->descriptionFr, $plugin->descriptionEn, $plugin->slug);
            self::assertContains($plugin->category, ['purchasing', 'billing', 'catalog', 'integration', 'compliance', 'admin'], $plugin->slug);
            self::assertSame('https://github.com/thibmonier/' . $plugin->slug, $plugin->githubUrl());
        }
    }

    public function testDescriptionFollowsTheLocale(): void
    {
        $plugin = self::catalog()->get('sylius-quick-order-plugin');

        self::assertNotNull($plugin);
        self::assertSame($plugin->descriptionFr, $plugin->description('fr_FR'));
        self::assertSame($plugin->descriptionEn, $plugin->description('en_US'));
        self::assertSame($plugin->descriptionEn, $plugin->description('de_DE'));
    }

    public function testAnUnknownSlugIsNull(): void
    {
        self::assertNull(self::catalog()->get('nope'));
    }

    public function testPluginsAreGroupedByCategory(): void
    {
        $groups = self::catalog()->byCategory();

        self::assertSame(21, array_sum(array_map('count', $groups)));
        self::assertArrayHasKey('purchasing', $groups);
    }

    public function testAnEntryWithoutDescriptionNamesTheFaultyPlugin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('sylius-broken-plugin');

        new DemoPluginCatalog([['slug' => 'sylius-broken-plugin', 'name' => 'Broken', 'category' => 'admin', 'description' => ['fr' => 'Seulement FR']]]);
    }

    public function testAnEntryWithoutNameNamesTheFaultyPlugin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('sylius-nameless-plugin');

        new DemoPluginCatalog([['slug' => 'sylius-nameless-plugin', 'category' => 'admin', 'description' => ['fr' => 'a', 'en' => 'b']]]);
    }

    private static function catalog(): DemoPluginCatalog
    {
        $config = Yaml::parseFile(\dirname(__DIR__, 4) . '/config/demo/plugins.yaml');
        self::assertIsArray($config);

        return new DemoPluginCatalog($config['parameters']['demo.plugin_catalog']);
    }
}
