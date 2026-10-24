<?php

declare(strict_types=1);

namespace App\Demo\Packs;

use Symfony\Component\Clock\ClockInterface;

/**
 * The whole generation of config/demo/packs.json (US-178): each pack repository. A repository that fails keeps the last
 * verified entry, marked stale with its date; the others carry on.
 */
final readonly class PacksRefresher
{
    /** @param list<string> $repositories */
    public function __construct(
        private PackCollector $collector,
        private ClockInterface $clock,
        private array $repositories = PackRepositories::ALL,
    ) {
    }

    /**
     * @param array<array-key, mixed> $current the file as it is now
     * @param list<string>            $only    refresh these packs only (the others keep their entry); all when empty
     */
    public function refresh(array $current, ?callable $report = null, array $only = []): PacksResult
    {
        $packs = [];
        foreach (\is_array($current['packs'] ?? null) ? $current['packs'] : [] as $name => $entry) {
            if (\is_array($entry) && \in_array((string) $name, $this->repositories, true)) {
                $packs[(string) $name] = $entry;
            }
        }
        $failures = [];
        foreach ([] === $only ? $this->repositories : $only as $name) {
            $collected = $this->collector->pack($name, $packs[$name] ?? []);
            if ([] !== $collected->facts) {
                $packs[$name] = $collected->facts;
            }
            if ([] !== $collected->failures) {
                $failures[$name] = $collected->failures;
            }
            if (null !== $report) {
                $report($name, $collected->failures);
            }
        }
        ksort($packs);

        return new PacksResult($this->clock->now()->format('Y-m-d\TH:i:s\Z'), $packs, $failures);
    }
}
