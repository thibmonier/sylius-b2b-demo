<?php

declare(strict_types=1);

namespace App\Tests\Unit\Stack;

use App\Demo\Stack\OutdatedSource;

/**
 * US-239: fake implementation of OutdatedSource for testing.
 * Returns predetermined data loaded from JSON fixtures.
 */
final readonly class FakeOutdatedSource implements OutdatedSource
{
    /**
     * @param array<int, array{name: string, version: string, latest: string, latest-status: string}> $composerOutdated
     * @param array<string, array{current: string, wanted: string, latest: string}> $npmOutdated
     * @param array<int, array{number: int, title: string}> $dependabotPRs
     */
    public function __construct(
        private array $composerOutdated = [],
        private array $npmOutdated = [],
        private array $dependabotPRs = [],
        private string $whyNotResponse = 'Constraint: requires package/dependency ^2.0',
    ) {
    }

    public static function fromJson(string $composerJson, string $npmJson, string $ghJson): self
    {
        $composerData = json_decode($composerJson, true, 512, \JSON_THROW_ON_ERROR);
        $npmData = json_decode($npmJson, true, 512, \JSON_THROW_ON_ERROR);
        $ghData = json_decode($ghJson, true, 512, \JSON_THROW_ON_ERROR);

        if (!is_array($composerData) || !is_array($npmData) || !is_array($ghData)) {
            throw new \InvalidArgumentException('Invalid JSON data');
        }

        $dependabotPRs = [];
        foreach ($ghData as $pr) {
            if (!is_array($pr)) {
                continue;
            }

            if (isset($pr['author']) && is_array($pr['author']) && isset($pr['author']['login']) && 'app/dependabot' === $pr['author']['login']) {
                if (!isset($pr['number'], $pr['title']) || !is_int($pr['number']) || !is_string($pr['title'])) {
                    continue;
                }

                $dependabotPRs[] = [
                    'number' => $pr['number'],
                    'title' => $pr['title'],
                ];
            }
        }

        /** @var array<int, array{name: string, version: string, latest: string, latest-status: string}> $installed */
        $installed = $composerData['installed'] ?? [];

        /** @var array<string, array{current: string, wanted: string, latest: string}> $npm */
        $npm = $npmData;

        return new self(
            composerOutdated: $installed,
            npmOutdated: $npm,
            dependabotPRs: $dependabotPRs,
        );
    }

    public function getComposerOutdated(): array
    {
        return $this->composerOutdated;
    }

    public function getNpmOutdated(): array
    {
        return $this->npmOutdated;
    }

    public function getDependabotPRs(): array
    {
        return $this->dependabotPRs;
    }

    public function getComposerWhyNot(string $package, string $version): string
    {
        return $this->whyNotResponse;
    }
}
