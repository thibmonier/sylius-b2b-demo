<?php

declare(strict_types=1);

namespace App\Demo\Packs;

/** One pack, as config/demo/packs.json holds it (US-178). */
final readonly class Pack
{
    /**
     * @param list<PackPlugin>     $plugins
     * @param list<PackSuggestion> $suggests
     * @param list<PackStep>       $steps
     */
    public function __construct(
        public string $repository,
        public string $composer,
        public string $command,
        private string $titleEn,
        private string $titleFr,
        private string $summaryEn,
        private string $summaryFr,
        public array $plugins,
        public array $suggests,
        public array $steps,
        public string $source,
        public string $composerSource,
        public string $checkedAt,
        public bool $stale,
    ) {
    }

    public function title(string $locale): string
    {
        return str_starts_with($locale, 'fr') ? $this->titleFr : $this->titleEn;
    }

    public function summary(string $locale): string
    {
        return str_starts_with($locale, 'fr') ? $this->summaryFr : $this->summaryEn;
    }

    /** @return list<string> */
    public function pluginSlugs(): array
    {
        return array_map(static fn (PackPlugin $plugin): string => $plugin->slug, $this->plugins);
    }

    /**
     * Counted from the steps, never stored: a bundle that two plugins declare is one gesture.
     *
     * @return array{bundles: int, config: int, routes: int, migrations: int}
     */
    public function totals(): array
    {
        return [
            'bundles' => \count(array_unique(array_merge(...array_map(static fn (PackStep $step): array => $step->bundles, $this->steps)))),
            'config' => array_sum(array_map(static fn (PackStep $step): int => \count($step->config), $this->steps)),
            'routes' => array_sum(array_map(static fn (PackStep $step): int => \count($step->routes), $this->steps)),
            'migrations' => \count(array_filter($this->steps, static fn (PackStep $step): bool => $step->migrations)),
        ];
    }
}
