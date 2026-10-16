<?php

declare(strict_types=1);

namespace App\Demo\Plugin;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Single source of truth for the showcased plugins (US-014). The data lives in
 * config/demo/plugins.yaml; the Plugins page, the About page and the plugin badges read it here.
 */
final class DemoPluginCatalog
{
    /** @var list<DemoPlugin> */
    private readonly array $plugins;

    /**
     * @param list<array<string, mixed>> $entries
     */
    public function __construct(
        #[Autowire(param: 'demo.plugin_catalog')]
        array $entries,
    ) {
        $this->plugins = array_map(self::plugin(...), $entries);
    }

    /** @return list<DemoPlugin> */
    public function all(): array
    {
        return $this->plugins;
    }

    public function get(string $slug): ?DemoPlugin
    {
        foreach ($this->plugins as $plugin) {
            if ($plugin->slug === $slug) {
                return $plugin;
            }
        }

        return null;
    }

    /** @return array<string, list<DemoPlugin>> plugins grouped by category, in catalogue order */
    public function byCategory(): array
    {
        $groups = [];
        foreach ($this->plugins as $plugin) {
            $groups[$plugin->category][] = $plugin;
        }

        return $groups;
    }

    /** @param array<string, mixed> $entry */
    private static function plugin(array $entry): DemoPlugin
    {
        $slug = self::string($entry, 'slug', '?');
        $description = $entry['description'] ?? null;
        if (!\is_array($description)) {
            throw new InvalidArgumentException(sprintf('Demo plugin "%s": "description" must define "fr" and "en".', $slug));
        }

        $route = $entry['demo_route'] ?? null;

        return new DemoPlugin(
            $slug,
            self::string($entry, 'name', $slug),
            self::string($entry, 'category', $slug),
            self::string($description, 'fr', $slug, 'description.fr'),
            self::string($description, 'en', $slug, 'description.en'),
            \is_string($route) && '' !== $route ? $route : null,
        );
    }

    /** @param array<array-key, mixed> $data */
    private static function string(array $data, string $key, string $plugin, ?string $label = null): string
    {
        $value = $data[$key] ?? null;
        if (!\is_string($value) || '' === trim($value)) {
            throw new InvalidArgumentException(sprintf('Demo plugin "%s": "%s" is required.', $plugin, $label ?? $key));
        }

        return $value;
    }
}
