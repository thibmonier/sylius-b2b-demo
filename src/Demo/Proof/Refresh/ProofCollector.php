<?php

declare(strict_types=1);

namespace App\Demo\Proof\Refresh;

use App\Demo\Proof\ProofReader;
use App\Demo\Proof\TestFileRule;
use Composer\Semver\Semver;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Collects the facts of the technical proof (US-111) from public sources only: the Packagist page of a package and the
 * GitHub REST API of a public repository (workflow file, latest completed run of the CI workflow, configuration, file tree).
 *
 * Rules: a fact is written only with a public source and the check date; a source that fails leaves the last verified
 * value in place, marked stale; nothing is estimated (no coverage is ever written: the CI does not publish the figure).
 */
final readonly class ProofCollector
{
    private const PACKAGIST_KEYS = ['version', 'sylius_declared', 'sylius_23'];

    private const PLUGIN_REPOSITORY_KEYS = ['ci', 'sylius_tested', 'phpstan', 'tests', 'coverage'];

    private const DELIVERY_REPOSITORY_KEYS = ['ci', 'tests', 'phpstan', 'ecs', 'composer_audit', 'image_scan', 'sbom', 'coverage'];

    public function __construct(
        private PackagistApi $packagist,
        private GitHubApi $github,
        private ClockInterface $clock,
        private string $owner = 'thibmonier',
        private string $vendor = 'tmonier',
    ) {
    }

    /** @param array<string, mixed> $previous the facts of this plugin in the current proof file */
    public function plugin(string $slug, array $previous = []): CollectedFacts
    {
        $facts = [];
        $failures = [];

        try {
            $facts += $this->packagistFacts($slug);
        } catch (SourceUnavailable) {
            $failures[] = 'packagist';
            $facts += self::kept($previous, self::PACKAGIST_KEYS);
        }

        try {
            $facts += $this->repositoryFacts($this->owner . '/' . $slug, false);
        } catch (SourceUnavailable) {
            $failures[] = 'github';
            $facts += self::kept($previous, self::PLUGIN_REPOSITORY_KEYS);
        }

        return new CollectedFacts($facts, $failures);
    }

    /**
     * @param array<string, mixed> $previous   the delivery facts of the current proof file
     * @param int|null             $localTests the test files of the local checkout (US-168): the demo publishes the count
     *                                         of the code it ships, so a pull request can carry a proof bin/proof-check accepts
     */
    public function delivery(string $repository, array $previous = [], ?int $localTests = null): CollectedFacts
    {
        try {
            $facts = $this->repositoryFacts($repository, true);
            if (null !== $localTests && \is_array($facts['tests'] ?? null)) {
                $facts['tests']['value'] = $localTests;
            }

            return new CollectedFacts($facts, []);
        } catch (SourceUnavailable) {
            return new CollectedFacts(self::kept($previous, self::DELIVERY_REPOSITORY_KEYS), ['github']);
        }
    }

    /**
     * @param array<string, mixed> $previous
     * @param list<string>         $keys
     *
     * @return array<string, array<string, mixed>>
     */
    private static function kept(array $previous, array $keys): array
    {
        $kept = [];
        foreach ($keys as $key) {
            if (\is_array($previous[$key] ?? null)) {
                $kept[$key] = ['stale' => true] + $previous[$key];
            }
        }

        return $kept;
    }

    /** @return array<string, array<string, mixed>> */
    private function packagistFacts(string $slug): array
    {
        $page = 'https://packagist.org/packages/' . $this->vendor . '/' . $slug;
        $package = $this->packagist->package($this->vendor . '/' . $slug);
        $versions = \is_array($package['versions'] ?? null) ? $package['versions'] : [];
        $latest = self::latestStable(array_keys($versions));
        if (null === $latest) {
            return ['version' => ['state' => 'unpublished', 'source' => $page, 'checked_at' => $this->today()]];
        }

        $facts = ['version' => $this->fact($latest, $page)];
        $release = $versions[$latest] ?? null;
        $require = \is_array($release) && \is_array($release['require'] ?? null) ? $release['require'] : [];
        $constraint = $require['sylius/sylius'] ?? null;
        if (\is_string($constraint) && '' !== $constraint) {
            $facts['sylius_declared'] = $this->fact($constraint, $page);

            try {
                $facts['sylius_23'] = $this->fact(Semver::satisfies('2.3.0', $constraint), $page);
            } catch (\UnexpectedValueException) {
                // a constraint Composer cannot parse: the declared text stays, the verdict is not given
            }
        }

        return $facts;
    }

    /** @param list<int|string> $versions */
    private static function latestStable(array $versions): ?string
    {
        $latest = null;
        foreach ($versions as $version) {
            $version = (string) $version;
            if (1 !== preg_match('/^v?\d+(?:\.\d+){0,3}$/', $version)) {
                continue;
            }
            if (null === $latest || version_compare(ltrim($version, 'v'), ltrim($latest, 'v'), '>')) {
                $latest = $version;
            }
        }

        return $latest;
    }

    /** @return array<string, array<string, mixed>> */
    private function repositoryFacts(string $repository, bool $delivery): array
    {
        $branch = $this->json('repos/' . $repository)['default_branch'] ?? null;
        if (!\is_string($branch) || 1 !== preg_match('/^[\w.\-]+$/', $branch)) {
            throw new SourceUnavailable('No default branch for ' . $repository);
        }
        $facts = [];
        $workflow = $this->workflow($repository, $branch);
        if (null !== $workflow) {
            $facts += $this->ciFact($repository, $branch, $workflow['name']);
            $facts += $this->workflowFacts($repository, $branch, $workflow, $delivery);
        }
        $facts += $this->testsFact($repository, $branch);
        $facts += $this->coverageFact($repository, $branch);

        return $facts;
    }

    /** @return array{name: string, content: string, url: string}|null */
    private function workflow(string $repository, string $branch): ?array
    {
        $names = [];
        foreach ($this->json('repos/' . $repository . '/contents/.github/workflows?ref=' . $branch) as $entry) {
            if (\is_array($entry) && \is_string($entry['name'] ?? null) && 1 === preg_match('/^[\w.\-]+\.ya?ml$/', $entry['name'])) {
                $names[] = $entry['name'];
            }
        }
        if ([] === $names) {
            return null;
        }
        sort($names);
        $preferred = array_values(array_intersect(['build.yaml', 'build.yml', 'ci.yaml', 'ci.yml'], $names));
        $name = $preferred[0] ?? $names[0];
        $content = $this->github->file($repository, '.github/workflows/' . $name, $branch);

        return null === $content ? null : ['name' => $name, 'content' => $content, 'url' => $this->blob($repository, $branch, '.github/workflows/' . $name)];
    }

    /** @return array<string, array<string, mixed>> */
    private function ciFact(string $repository, string $branch, string $workflowName): array
    {
        $runs = $this->json('repos/' . $repository . '/actions/workflows/' . $workflowName . '/runs?branch=' . $branch . '&status=completed&per_page=1&exclude_pull_requests=true')['workflow_runs'] ?? null;
        $run = \is_array($runs) && \is_array($runs[0] ?? null) ? $runs[0] : null;
        if (null === $run || !\is_string($run['conclusion'] ?? null) || !\is_string($run['html_url'] ?? null)) {
            return [];
        }

        return ['ci' => $this->fact($run['conclusion'], $run['html_url'])];
    }

    /**
     * @param array{name: string, content: string, url: string} $workflow
     *
     * @return array<string, array<string, mixed>>
     */
    private function workflowFacts(string $repository, string $branch, array $workflow, bool $delivery): array
    {
        $text = strtolower($workflow['content']);
        $facts = [];
        $level = str_contains($text, 'phpstan') ? $this->phpstanLevel($repository, $branch) : null;
        if (null !== $level) {
            $facts['phpstan'] = $this->fact($level['level'], $level['url']);
        }
        if (!$delivery) {
            $tested = self::syliusVersions($workflow['content']);
            if (null !== $tested) {
                $facts['sylius_tested'] = $this->fact($tested, $workflow['url']);
            }

            return $facts;
        }
        foreach (['ecs' => ['ecs check', 'ECS'], 'composer_audit' => ['composer audit', 'composer audit'], 'image_scan' => ['trivy', 'Trivy'], 'sbom' => ['cyclonedx', 'CycloneDX']] as $key => [$needle, $label]) {
            if (str_contains($text, $needle)) {
                $facts[$key] = $this->fact($label, $workflow['url']);
            }
        }

        return $facts;
    }

    /** @return array{level: string, url: string}|null */
    private function phpstanLevel(string $repository, string $branch): ?array
    {
        foreach (['phpstan.neon', 'phpstan.dist.neon'] as $file) {
            $content = $this->github->file($repository, $file, $branch);
            if (null !== $content && 1 === preg_match('/^\s*level:\s*([A-Za-z0-9]+)\s*$/m', $content, $matches)) {
                return ['level' => $matches[1], 'url' => $this->blob($repository, $branch, $file)];
            }
        }

        return null;
    }

    /** The Sylius versions a CI matrix runs, as "2.2, 2.3" (from "~2.2.0", "~2.3.0"); null when the workflow names none. */
    private static function syliusVersions(string $workflow): ?string
    {
        try {
            $data = Yaml::parse($workflow);
        } catch (ParseException) {
            return null;
        }
        $versions = [];
        $jobs = \is_array($data) && \is_array($data['jobs'] ?? null) ? $data['jobs'] : [];
        foreach ($jobs as $job) {
            $matrix = \is_array($job) && \is_array($job['strategy'] ?? null) && \is_array($job['strategy']['matrix'] ?? null) ? $job['strategy']['matrix'] : [];
            $declared = [$matrix['sylius'] ?? null];
            foreach (\is_array($matrix['include'] ?? null) ? $matrix['include'] : [] as $include) {
                $declared[] = \is_array($include) ? ($include['sylius'] ?? null) : null;
            }
            foreach (array_merge(...array_map(static fn (mixed $value): array => \is_array($value) ? $value : [$value], $declared)) as $constraint) {
                if (\is_string($constraint) && 1 === preg_match('/(\d+\.\d+)/', $constraint, $matches)) {
                    $versions[$matches[1]] = true;
                }
            }
        }
        $versions = array_map('strval', array_keys($versions));
        usort($versions, static fn (string $a, string $b): int => version_compare($a, $b));

        return [] === $versions ? null : implode(', ', $versions);
    }

    /** @return array<string, array<string, mixed>> */
    private function testsFact(string $repository, string $branch): array
    {
        $tree = $this->json('repos/' . $repository . '/git/trees/' . $branch . '?recursive=1');
        if (true === ($tree['truncated'] ?? true) || !\is_array($tree['tree'] ?? null)) {
            throw new SourceUnavailable('The file tree of ' . $repository . ' is incomplete');
        }
        $count = 0;
        foreach ($tree['tree'] as $entry) {
            if (\is_array($entry) && 'blob' === ($entry['type'] ?? null) && \is_string($entry['path'] ?? null) && TestFileRule::matches($entry['path'])) {
                ++$count;
            }
        }

        return ['tests' => $this->fact($count, 'https://github.com/' . $repository . '/tree/' . $branch . '/tests')];
    }

    /** @return array<string, array<string, mixed>> */
    private function coverageFact(string $repository, string $branch): array
    {
        try {
            $checkRuns = $this->json('repos/' . $repository . '/commits/' . $branch . '/check-runs?check_name=coverage&per_page=1');
        } catch (SourceUnavailable) {
            return [];
        }
        $runs = \is_array($checkRuns['check_runs'] ?? null) ? $checkRuns['check_runs'] : [];
        $run = \is_array($runs[0] ?? null) ? $runs[0] : null;
        if (null === $run) {
            return [];
        }
        $summary = \is_array($run['output'] ?? null) && \is_string($run['output']['summary'] ?? null) ? $run['output']['summary'] : '';
        if (1 !== preg_match('/COVERAGE_LINES:\s*([\d.]+)/', $summary, $matches)) {
            return [];
        }
        $url = \is_string($run['html_url'] ?? null) ? $run['html_url'] : 'https://github.com/' . $repository;

        return ['coverage' => $this->fact((float) $matches[1], $url)];
    }

    /** @return array<array-key, mixed> */
    private function json(string $path): array
    {
        return $this->github->json($path);
    }

    private function blob(string $repository, string $branch, string $path): string
    {
        return 'https://github.com/' . $repository . '/blob/' . $branch . '/' . $path;
    }

    /** @return array<string, mixed> */
    private function fact(string|int|float|bool $value, string $source): array
    {
        if (!ProofReader::isPublicSource($source)) {
            throw new SourceUnavailable('Refusing a source that is not public: ' . $source);
        }

        return ['value' => $value, 'source' => $source, 'checked_at' => $this->today()];
    }

    private function today(): string
    {
        return $this->clock->now()->format('Y-m-d');
    }
}
