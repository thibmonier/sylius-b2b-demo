<?php

declare(strict_types=1);

namespace App\Demo\Proof\Compatibility\Refresh;

use App\Demo\Proof\Refresh\SourceUnavailable;
use Symfony\Component\Clock\ClockInterface;

/**
 * The whole generation of config/demo/compatibility.json (US-171): each plugin repository, and the stack the demo itself
 * installs. A source that fails keeps the last verified value, marked stale with its date; the others carry on.
 */
final readonly class CompatibilityRefresher
{
    public function __construct(
        private CompatibilityCollector $collector,
        private ClockInterface $clock,
        private string $demoRepository = 'thibmonier/sylius-b2b-demo',
    ) {
    }

    /**
     * @param array<array-key, mixed> $current the file as it is now
     * @param list<string>            $slugs   the plugins to refresh (the others keep their facts)
     * @param string|null             $lock    the composer.lock of the demo, null when it cannot be read
     */
    public function refresh(array $current, array $slugs, ?string $lock, ?callable $report = null): CompatibilityResult
    {
        $plugins = [];
        foreach (\is_array($current['plugins'] ?? null) ? $current['plugins'] : [] as $slug => $facts) {
            $plugins[(string) $slug] = \is_array($facts) ? $facts : [];
        }
        $failures = [];
        $stack = $this->stack($lock);
        foreach ($slugs as $slug) {
            $collected = $this->collector->plugin($slug, $plugins[$slug] ?? []);
            $facts = $collected->facts;
            if (null !== $stack && isset($stack['plugins'][$slug])) {
                $facts['demo'] = ['version' => $stack['plugins'][$slug]] + $this->lockStamp();
            } elseif (null === $stack && \is_array($plugins[$slug]['demo'] ?? null)) {
                $facts['demo'] = ['stale' => true] + $plugins[$slug]['demo'];
            }
            $plugins[$slug] = $facts;
            $this->note($failures, $report, $slug, $collected->failures);
        }
        $demo = $this->demo($stack, $current['demo'] ?? null);
        $this->note($failures, $report, 'demo', null === $stack ? ['composer.lock'] : []);
        ksort($plugins);

        return new CompatibilityResult($this->clock->now()->format('Y-m-d\TH:i:s\Z'), $demo, $plugins, $failures);
    }

    /** @return array{sylius: string, symfony: string, php: ?string, plugins: array<string, string>}|null */
    private function stack(?string $lock): ?array
    {
        try {
            return null === $lock ? null : DemoStack::fromLock($lock);
        } catch (SourceUnavailable) {
            return null;
        }
    }

    /**
     * @param array{sylius: string, symfony: string, php: ?string, plugins: array<string, string>}|null $stack
     *
     * @return array<string, mixed>|null
     */
    private function demo(?array $stack, mixed $previous): ?array
    {
        if (null === $stack) {
            return \is_array($previous) ? ['stale' => true] + $previous : null;
        }

        return ['sylius' => $stack['sylius'], 'symfony' => $stack['symfony'], 'php' => $stack['php']] + $this->lockStamp();
    }

    /** @return array{source: string, checked_at: string} */
    private function lockStamp(): array
    {
        return ['source' => 'https://github.com/' . $this->demoRepository . '/blob/main/composer.lock', 'checked_at' => $this->clock->now()->format('Y-m-d')];
    }

    /**
     * @param array<string, list<string>> $failures
     * @param list<string>                $sources
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
