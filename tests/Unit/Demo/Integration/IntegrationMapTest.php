<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Integration;

use App\Demo\Account\DemoAccountCatalog;
use App\Demo\Integration\Integration;
use App\Demo\Integration\IntegrationMap;
use App\Demo\Plugin\DemoPluginCatalog;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/** US-108: the state of an integration comes from the plugin catalogue (US-087), never from a second source of truth. */
final class IntegrationMapTest extends TestCase
{
    public function testAPublishedPluginIsAvailable(): void
    {
        $map = $this->map([['id' => 'portals', 'plugin' => 'published-one', 'state' => 'available']]);

        self::assertSame(Integration::AVAILABLE, $map->all()[0]->state);
        self::assertSame('published-one', $map->all()[0]->plugin?->slug);
    }

    public function testAnUnpublishedInstalledPluginIsInProgressAndANotInstalledOneIsUpcoming(): void
    {
        $map = $this->map([
            ['id' => 'a', 'plugin' => 'in-progress-one', 'state' => 'in_progress'],
            ['id' => 'b', 'plugin' => 'upcoming-one', 'state' => 'upcoming'],
        ]);

        self::assertSame([Integration::IN_PROGRESS, Integration::UPCOMING], array_map(static fn (Integration $i): string => $i->state, $map->all()));
    }

    public function testAnIntegrationWithoutPluginIsNotProvidedAndPointsToTheEventContract(): void
    {
        $map = $this->map([['id' => 'erp', 'plugin' => null, 'contract' => 'published-one', 'state' => 'not_provided']]);

        $integration = $map->all()[0];
        self::assertSame(Integration::NOT_PROVIDED, $integration->state);
        self::assertNull($integration->plugin);
        self::assertSame('published-one', $integration->contract?->slug);
        self::assertSame([], $map->inconsistencies());
    }

    public function testAPublishedPluginMarkedUpcomingIsAnInconsistency(): void
    {
        $map = $this->map([['id' => 'portals', 'plugin' => 'published-one', 'state' => 'upcoming']]);

        $problems = $map->inconsistencies();

        self::assertCount(1, $problems);
        self::assertStringContainsString('portals', $problems[0]);
    }

    public function testAnUnknownPluginOrANotProvidedIntegrationWithoutContractIsAnInconsistency(): void
    {
        $map = $this->map([
            ['id' => 'ghost', 'plugin' => 'nope', 'state' => 'available'],
            ['id' => 'erp', 'plugin' => null, 'state' => 'not_provided'],
        ]);

        $problems = implode("\n", $map->inconsistencies());
        self::assertStringContainsString('unknown plugin "nope"', $problems);
        self::assertStringContainsString('Integration "erp": not provided', $problems);
    }

    public function testTheRealConfigurationIsCoherentAndCoversEveryFamily(): void
    {
        $root = \dirname(__DIR__, 4);
        $plugins = new DemoPluginCatalog(self::parameter($root . '/config/demo/plugins.yaml', 'demo.plugin_catalog'), self::accounts());
        $map = new IntegrationMap(self::parameter($root . '/config/demo/integrations.yaml', 'demo.integrations'), $plugins);

        self::assertSame([], $map->inconsistencies());
        $ids = array_map(static fn (Integration $i): string => $i->id, $map->all());
        foreach (['erp', 'pim', 'carriers', 'einvoicing_format', 'einvoicing_platform', 'purchasing_portals', 'ai_assistants'] as $family) {
            self::assertContains($family, $ids);
        }
    }

    /** @param list<array<string, mixed>> $entries */
    private function map(array $entries): IntegrationMap
    {
        $plugin = static fn (string $slug, array $flags): array => [
            'slug' => $slug, 'name' => $slug, 'category' => 'integration',
            'description' => ['fr' => 'd', 'en' => 'd'], 'demo_route' => null,
            'scenario' => ['fr' => 's', 'en' => 's'], 'advised_account' => 'sophie@fournitures-pro.example',
        ] + $flags;
        $catalog = new DemoPluginCatalog([
            $plugin('published-one', []),
            $plugin('in-progress-one', ['published' => false]),
            $plugin('upcoming-one', ['published' => false, 'installed' => false]),
        ], self::accounts());

        return new IntegrationMap($entries, $catalog);
    }

    private static function accounts(): DemoAccountCatalog
    {
        $config = Yaml::parseFile(\dirname(__DIR__, 4) . '/config/demo/accounts.yaml');
        self::assertIsArray($config);
        $p = $config['parameters'];

        return new DemoAccountCatalog($p['demo.password'], $p['demo.companies'], $p['demo.consumers'], $p['demo.admin_users']);
    }

    /** @return list<array<string, mixed>> */
    private static function parameter(string $file, string $name): array
    {
        $data = Yaml::parseFile($file);
        self::assertIsArray($data);
        $value = $data['parameters'][$name] ?? null;
        self::assertIsArray($value);

        return array_values($value);
    }
}
