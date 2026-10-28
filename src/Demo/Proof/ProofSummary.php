<?php

declare(strict_types=1);

namespace App\Demo\Proof;

/**
 * US-205: the figures of the summary tiles, counted in the report (never written by hand). Only verified facts are
 * counted: a fact that is unpublished, unmeasured or unavailable adds nothing, so a figure can never be higher than the proof.
 */
final readonly class ProofSummary
{
    /** The PHPStan level the tile counts: the strictest one, as the plugins' configuration files name it. */
    private const PHPSTAN_STRICTEST = 'max';

    private const CI_PASSED = 'success';

    public function __construct(
        public int $plugins,
        public int $versions,
        public int $sylius23,
        public int $ciPassed,
        public int $phpstanMax,
        public int $testFiles,
        public int $pluginsWithTestFiles,
        public int $coverage,
        public ?int $demoTestFiles,
    ) {
    }

    /** @param list<string> $slugs the plugins of the catalogue */
    public static function of(ProofReport $report, array $slugs): self
    {
        $count = static fn (callable $matches): int => \count(array_filter($slugs, $matches));
        $verified = static fn (string $slug, string $key): ?ProofFact => ($fact = $report->fact($slug, $key))->isVerified() ? $fact : null;
        $testFiles = array_map(static fn (string $slug): int => \is_int($value = $verified($slug, 'tests')?->value) ? $value : 0, $slugs);
        $demoTests = $report->deliveryFact('tests');

        return new self(
            \count($slugs),
            $count(static fn (string $slug): bool => null !== $verified($slug, 'version')),
            $count(static fn (string $slug): bool => true === $verified($slug, 'sylius_23')?->value),
            $count(static fn (string $slug): bool => self::CI_PASSED === $verified($slug, 'ci')?->value),
            $count(static fn (string $slug): bool => self::PHPSTAN_STRICTEST === $verified($slug, 'phpstan')?->value),
            array_sum($testFiles),
            \count(array_filter($testFiles)),
            $count(static fn (string $slug): bool => null !== $verified($slug, 'coverage')),
            $demoTests->isVerified() && \is_int($demoTests->value) ? $demoTests->value : null,
        );
    }

    public function hasCoverage(): bool
    {
        return $this->coverage > 0;
    }
}
