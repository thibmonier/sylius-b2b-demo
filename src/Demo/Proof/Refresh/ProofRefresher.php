<?php

declare(strict_types=1);

namespace App\Demo\Proof\Refresh;

use Symfony\Component\Clock\ClockInterface;

/** Builds the next content of config/demo/proof.json from the current one: only the requested subjects are refreshed. */
final readonly class ProofRefresher
{
    public function __construct(private ProofCollector $collector, private ClockInterface $clock)
    {
    }

    /**
     * @param array<array-key, mixed>                     $current        the decoded current file (empty when there is none)
     * @param list<string>                                $slugs          the plugins to refresh; the others keep their facts
     * @param string|null                                 $demoRepository "owner/name" of the demo, refreshed too when given
     * @param (callable(string, list<string>): void)|null $report         called per subject with its failed sources
     */
    public function refresh(array $current, array $slugs, ?string $demoRepository, ?callable $report = null): RefreshResult
    {
        $plugins = [];
        $currentPlugins = \is_array($current['plugins'] ?? null) ? $current['plugins'] : [];
        foreach ($currentPlugins as $slug => $facts) {
            $plugins[(string) $slug] = self::facts($facts);
        }
        $delivery = self::facts($current['delivery'] ?? null);
        $failures = [];
        foreach ($slugs as $slug) {
            $collected = $this->collector->plugin($slug, $plugins[$slug] ?? []);
            $plugins[$slug] = $collected->facts;
            $this->note($failures, $report, $slug, $collected->failures);
        }
        if (null !== $demoRepository) {
            $collected = $this->collector->delivery($demoRepository, $delivery);
            $delivery = $collected->facts;
            $this->note($failures, $report, 'delivery', $collected->failures);
        }
        ksort($plugins);

        return new RefreshResult($this->clock->now()->format('Y-m-d\TH:i:s\Z'), $plugins, $delivery, $failures);
    }

    /** @return array<string, array<string, mixed>> */
    private static function facts(mixed $facts): array
    {
        $result = [];
        foreach (\is_array($facts) ? $facts : [] as $key => $fact) {
            if (\is_array($fact)) {
                $result[(string) $key] = array_combine(array_map('strval', array_keys($fact)), $fact);
            }
        }

        return $result;
    }

    /**
     * @param array<string, list<string>>                 $failures
     * @param (callable(string, list<string>): void)|null $report
     * @param list<string>                                $sources
     */
    private function note(array &$failures, ?callable $report, string $subject, array $sources): void
    {
        if ([] !== $sources) {
            $failures[$subject] = $sources;
        }
        if (null !== $report) {
            $report($subject, $sources);
        }
    }
}
