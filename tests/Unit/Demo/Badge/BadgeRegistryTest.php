<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Badge;

use App\Demo\Plugin\DemoPlugin;
use App\Demo\Plugin\DemoPluginCatalog;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

/** US-016 / US-017 — the badge registry (config/packages/demo_plugin_badges.yaml) only points to real plugins and real hooks. */
final class BadgeRegistryTest extends KernelTestCase
{
    private const ROOT = __DIR__ . '/../../../..';

    public function testEveryBadgeReferencesAPluginOfThePluginsPage(): void
    {
        $catalog = self::catalog();

        foreach (self::entries() as $badge) {
            self::assertInstanceOf(DemoPlugin::class, $catalog->get($badge['plugin']), \sprintf('Badge "%s" on "%s" references the unknown plugin "%s".', $badge['name'], $badge['hook'], $badge['plugin']));
        }
    }

    public function testEveryBadgeSitsOnAHookThePluginItselfRegisters(): void
    {
        foreach (self::entries() as $badge) {
            $files = glob(self::ROOT . '/vendor/tmonier/' . $badge['plugin'] . '/config/twig_hooks/*.yaml');
            self::assertNotEmpty($files, $badge['plugin']);
            $hooks = [];
            foreach ($files as $file) {
                $hooks = [...$hooks, ...array_keys(self::map(self::map(self::map(Yaml::parseFile($file))['sylius_twig_hooks'] ?? null)['hooks'] ?? null))];
            }

            self::assertContains($badge['hook'], $hooks, \sprintf('Badge "%s": %s registers no hookable on "%s".', $badge['name'], $badge['plugin'], $badge['hook']));
        }
    }

    public function testShopAndAdminBadgesCoverTheMainPlugins(): void
    {
        $shop = $admin = [];
        foreach (self::entries() as $badge) {
            if (str_starts_with($badge['hook'], 'sylius_admin.')) {
                $admin[] = $badge['plugin'];
            } else {
                $shop[] = $badge['plugin'];
            }
        }

        foreach (['sylius-quote-request-plugin', 'sylius-quick-order-plugin', 'sylius-tier-pricing-plugin', 'sylius-gpsr-plugin', 'sylius-withdrawal-plugin', 'sylius-purchase-approval-plugin'] as $slug) {
            self::assertContains($slug, $shop);
        }
        foreach (['sylius-timeline-plugin', 'sylius-audit-log-plugin', 'sylius-purchase-approval-plugin', 'sylius-facturx-plugin'] as $slug) {
            self::assertContains($slug, $admin);
        }
    }

    public function testAnUnknownPluginIsNullSoNothingIsRendered(): void
    {
        self::assertNull(self::catalog()->get('sylius-not-a-plugin'));
    }

    /** @return list<array{hook: string, name: string, plugin: string}> */
    private static function entries(): array
    {
        $entries = [];
        foreach (['demo_plugin_badges.yaml', 'demo_plugin_badges_admin.yaml'] as $file) {
            if (!is_file(self::ROOT . '/config/packages/' . $file)) {
                continue;
            }
            $hooks = self::map(self::map(self::map(Yaml::parseFile(self::ROOT . '/config/packages/' . $file))['sylius_twig_hooks'] ?? null)['hooks'] ?? null);
            foreach ($hooks as $hook => $hookables) {
                foreach (self::map($hookables) as $name => $entry) {
                    $entry = self::map($entry);
                    $plugin = self::map($entry['configuration'] ?? null)['plugin'] ?? null;
                    if ('demo/plugin_badge.html.twig' === ($entry['template'] ?? null) && \is_string($plugin)) {
                        $entries[] = ['hook' => (string) $hook, 'name' => (string) $name, 'plugin' => $plugin];
                    }
                }
            }
        }
        self::assertNotEmpty($entries);

        return $entries;
    }

    /** @return array<array-key, mixed> */
    private static function map(mixed $value): array
    {
        return \is_array($value) ? $value : [];
    }

    private static function catalog(): DemoPluginCatalog
    {
        self::bootKernel();
        $catalog = self::getContainer()->get(DemoPluginCatalog::class);
        self::assertInstanceOf(DemoPluginCatalog::class, $catalog);

        return $catalog;
    }
}
