<?php

declare(strict_types=1);

namespace App\Demo\Stack;

use Composer\Semver\Semver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Reads the FrankenPHP tag list from the Docker Hub API and the php constraints from composer.lock.
 * Network failures are reported as "no tag": the watch then stays closed rather than announcing a
 * false green, which is the safe direction for a gate.
 */
final readonly class DockerHubPhpUpgradeWatchSource implements PhpUpgradeWatchSource
{
    private const string TAGS_URL = 'https://hub.docker.com/v2/repositories/dunglas/frankenphp/tags';

    public function __construct(
        private HttpClientInterface $http,
        private string $lockPath,
    ) {
    }

    public function frankenPhpTagCount(string $imageTag): int
    {
        try {
            $payload = $this->http->request('GET', self::TAGS_URL, [
                'query' => ['page_size' => 1, 'name' => $imageTag],
                'timeout' => 15,
            ])->toArray();
        } catch (\Throwable) {
            return 0;
        }

        return \is_int($payload['count'] ?? null) ? $payload['count'] : 0;
    }

    public function packagesExcluding(string $phpVersion): array
    {
        $excluding = [];

        foreach ($this->lockedPackages() as $package) {
            $name = $package['name'] ?? null;
            $require = $package['require'] ?? null;
            $constraint = \is_array($require) ? $require['php'] ?? null : null;
            if (\is_string($name) && \is_string($constraint) && !$this->allows($constraint, $phpVersion)) {
                $excluding[$name] = $constraint;
            }
        }

        ksort($excluding);

        return $excluding;
    }

    /** @return list<array<mixed, mixed>> */
    private function lockedPackages(): array
    {
        $lock = json_decode((string) file_get_contents($this->lockPath), true, flags: \JSON_THROW_ON_ERROR);
        if (!\is_array($lock)) {
            return [];
        }

        $packages = [];
        foreach (['packages', 'packages-dev'] as $key) {
            $section = $lock[$key] ?? [];
            if (\is_array($section)) {
                foreach ($section as $package) {
                    if (\is_array($package)) {
                        $packages[] = $package;
                    }
                }
            }
        }

        return $packages;
    }

    private function allows(string $constraint, string $phpVersion): bool
    {
        try {
            return Semver::satisfies($phpVersion, $constraint);
        } catch (\Throwable) {
            // An unparseable constraint is never a reason to hold the gate shut: it is reported nowhere
            // and treated as permissive, the same way Composer's own solver would have failed loudly first.
            return true;
        }
    }
}
