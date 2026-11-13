<?php

declare(strict_types=1);

namespace App\Demo\Stack;

/**
 * Reads the plugin repositories with the GitHub CLI: open Dependabot pull requests with their checks,
 * and the check runs of the default branch. The repositories are public, so the workflow token is enough.
 */
final readonly class GhPluginRepositoriesSource implements PluginRepositoriesSource
{
    private const array RED = ['FAILURE', 'TIMED_OUT', 'ERROR', 'ACTION_REQUIRED', 'STARTUP_FAILURE'];

    /** @var \Closure(list<string>): ?string */
    private \Closure $run;

    /**
     * @param list<string>                         $repositories
     * @param \Closure(list<string>): ?string|null $run         runs a command, returns its standard output or null on failure
     */
    public function __construct(
        private string $owner,
        private array $repositories,
        ?\Closure $run = null,
    ) {
        $this->run = $run ?? self::shell(...);
    }

    public function statuses(): array
    {
        return array_map(fn (string $repository): PluginRepositoryStatus => new PluginRepositoryStatus(
            $repository,
            $this->mainGreen($repository),
            $this->pullRequests($repository),
        ), $this->repositories);
    }

    private function mainGreen(string $repository): ?bool
    {
        $runs = $this->json(['gh', 'api', "repos/{$this->owner}/$repository/commits/main/check-runs?per_page=100"]);
        $conclusions = array_map(
            static fn (mixed $run): string => strtoupper(\is_array($run) && \is_string($run['conclusion'] ?? null) ? $run['conclusion'] : ''),
            array_filter(\is_array($runs['check_runs'] ?? null) ? $runs['check_runs'] : [], self::isCi(...)),
        );

        return [] === $conclusions ? null : [] === array_intersect($conclusions, self::RED);
    }

    /** Dependabot reports its own update jobs as check runs on the head of main: they say nothing about the CI. */
    private static function isCi(mixed $run): bool
    {
        return !\is_array($run) || 'dependabot' !== (\is_array($run['app'] ?? null) ? ($run['app']['slug'] ?? null) : null);
    }

    /** @return list<DependabotPullRequest> */
    private function pullRequests(string $repository): array
    {
        $prs = $this->json(['gh', 'pr', 'list', '-R', "{$this->owner}/$repository", '--author', 'app/dependabot', '--state', 'open', '--json', 'number,title,statusCheckRollup']) ?? [];
        $result = [];
        foreach ($prs as $pr) {
            if (\is_array($pr) && \is_int($pr['number'] ?? null) && \is_string($pr['title'] ?? null)) {
                $result[] = new DependabotPullRequest($pr['number'], $pr['title'], self::checks(\is_array($pr['statusCheckRollup'] ?? null) ? $pr['statusCheckRollup'] : []));
            }
        }

        return $result;
    }

    /**
     * Check runs carry status + conclusion, commit statuses carry a state.
     *
     * @param array<mixed> $rollup
     *
     * @return DependabotPullRequest::CHECKS_*
     */
    private static function checks(array $rollup): string
    {
        $pending = [] === $rollup;
        foreach ($rollup as $check) {
            $check = \is_array($check) ? $check : [];
            $outcome = $check['conclusion'] ?? $check['state'] ?? '';
            $outcome = \is_string($outcome) ? strtoupper($outcome) : '';
            if (\in_array($outcome, self::RED, true)) {
                return DependabotPullRequest::CHECKS_FAILURE;
            }
            $pending = $pending || 'PENDING' === $outcome || ('' === $outcome && 'COMPLETED' !== ($check['status'] ?? null));
        }

        return $pending ? DependabotPullRequest::CHECKS_PENDING : DependabotPullRequest::CHECKS_SUCCESS;
    }

    /**
     * @param list<string> $command
     *
     * @return array<mixed>|null
     */
    private function json(array $command): ?array
    {
        $output = ($this->run)($command);
        $data = null === $output ? null : json_decode($output, true);

        return \is_array($data) ? $data : null;
    }

    /** @param list<string> $command */
    private static function shell(array $command): ?string
    {
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (!\is_resource($process)) {
            return null;
        }
        $output = (string) stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);

        return 0 === proc_close($process) ? $output : null;
    }
}
