<?php

declare(strict_types=1);

namespace App\Demo\Integration;

use App\Demo\Plugin\DemoPlugin;
use App\Demo\Plugin\DemoPluginCatalog;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * US-108: the integrations of the guide page and their state, derived from the plugin catalogue (the plugin status of US-087 is the
 * single source). The configuration declares the state it expects; inconsistencies() lists every disagreement.
 */
final class IntegrationMap
{
    /** @var list<Integration> */
    private readonly array $integrations;

    /** @var list<string> */
    private array $problems = [];

    /** @param list<array<string, mixed>> $entries */
    public function __construct(
        #[Autowire(param: 'demo.integrations')]
        array $entries,
        private readonly DemoPluginCatalog $plugins,
    ) {
        $this->integrations = array_map($this->integration(...), $entries);
    }

    /** @return list<Integration> */
    public function all(): array
    {
        return $this->integrations;
    }

    /** @return list<string> */
    public function inconsistencies(): array
    {
        return $this->problems;
    }

    /** @param array<string, mixed> $entry */
    private function integration(array $entry): Integration
    {
        $id = \is_string($entry['id'] ?? null) ? $entry['id'] : '?';
        $plugin = $this->plugin($id, $entry['plugin'] ?? null);
        $contract = $this->plugin($id, $entry['contract'] ?? null);
        $state = null === $plugin ? Integration::NOT_PROVIDED : $this->stateOf($plugin);
        if (Integration::NOT_PROVIDED === $state && null === $contract) {
            $this->problems[] = \sprintf('Integration "%s": not provided, so it must point to the plugin whose event contract applies.', $id);
        }
        if (($entry['state'] ?? null) !== $state) {
            $this->problems[] = \sprintf('Integration "%s": declared "%s" but its plugin status gives "%s".', $id, \is_string($entry['state'] ?? null) ? $entry['state'] : '?', $state);
        }

        return new Integration($id, $state, $plugin, $contract);
    }

    private function plugin(string $id, mixed $slug): ?DemoPlugin
    {
        if (!\is_string($slug) || '' === $slug) {
            return null;
        }
        $plugin = $this->plugins->get($slug);
        if (null === $plugin) {
            $this->problems[] = \sprintf('Integration "%s": unknown plugin "%s".', $id, $slug);
        }

        return $plugin;
    }

    private function stateOf(DemoPlugin $plugin): string
    {
        return match ($plugin->status()) {
            null => Integration::AVAILABLE,
            'in_progress' => Integration::IN_PROGRESS,
            default => Integration::UPCOMING,
        };
    }
}
