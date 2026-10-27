<?php

declare(strict_types=1);

namespace App\Demo\Plugin;

use App\Demo\Account\DemoAccountCatalog;
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
        DemoAccountCatalog $accounts,
    ) {
        $emails = array_map(
            static fn ($account): string => $account->email,
            [...$accounts->shopAccounts(), ...$accounts->adminUsers()],
        );
        $this->plugins = array_map(static fn (array $entry): DemoPlugin => self::plugin($entry, $emails), $entries);
    }

    /** @return list<DemoPlugin> */
    public function all(): array
    {
        return $this->plugins;
    }

    /** The number of showcased plugins: the catalogue is the only source of the figure displayed in the pages. */
    public function count(): int
    {
        return \count($this->plugins);
    }

    /**
     * The plugins shown on the public Plugins page; the figure of the other pages keeps counting all of them (count()).
     *
     * @return list<DemoPlugin>
     */
    public function listed(): array
    {
        return array_values(array_filter($this->plugins, static fn (DemoPlugin $plugin): bool => $plugin->listed));
    }

    public function countListed(): int
    {
        return \count($this->listed());
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
        return self::group($this->plugins);
    }

    /** @return array<string, non-empty-list<DemoPlugin>> */
    public function listedByCategory(): array
    {
        return self::group($this->listed());
    }

    /**
     * @param list<DemoPlugin> $plugins
     *
     * @return array<string, non-empty-list<DemoPlugin>>
     */
    private static function group(array $plugins): array
    {
        $groups = [];
        foreach ($plugins as $plugin) {
            $groups[$plugin->category][] = $plugin;
        }

        return $groups;
    }

    /**
     * @param array<string, mixed> $entry
     * @param list<string>         $accountEmails
     */
    private static function plugin(array $entry, array $accountEmails): DemoPlugin
    {
        $slug = self::string($entry, 'slug', '?');
        $description = $entry['description'] ?? null;
        if (!\is_array($description)) {
            throw new InvalidArgumentException(sprintf('Demo plugin "%s": "description" must define "fr" and "en".', $slug));
        }

        $scenario = $entry['scenario'] ?? null;
        if (!\is_array($scenario)) {
            throw new InvalidArgumentException(sprintf('Demo plugin "%s": "scenario" must define "fr" and "en".', $slug));
        }

        $account = self::string($entry, 'advised_account', $slug);
        if (!\in_array($account, $accountEmails, true)) {
            throw new InvalidArgumentException(sprintf('Demo plugin "%s": advised account "%s" is not a demo account.', $slug, $account));
        }

        $route = $entry['demo_route'] ?? null;

        return new DemoPlugin(
            $slug,
            self::string($entry, 'name', $slug),
            self::string($entry, 'category', $slug),
            self::string($description, 'fr', $slug, 'description.fr'),
            self::string($description, 'en', $slug, 'description.en'),
            \is_string($route) && '' !== $route ? $route : null,
            self::string($scenario, 'fr', $slug, 'scenario.fr'),
            self::string($scenario, 'en', $slug, 'scenario.en'),
            $account,
            ($entry['published'] ?? true) !== false,
            ($entry['installed'] ?? true) !== false,
            ($entry['listed'] ?? true) !== false,
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
