<?php

declare(strict_types=1);

namespace App\Demo\Stack;

/**
 * US-239: shell-based implementation of OutdatedSource.
 * Executes composer, npm, and gh commands to fetch real data.
 */
final readonly class ShellOutdatedSource implements OutdatedSource
{
    public function getComposerOutdated(): array
    {
        $output = shell_exec('composer outdated --direct --format=json --no-ansi 2>/dev/null');

        if (null === $output || false === $output || '' === $output) {
            return [];
        }

        try {
            $data = json_decode($output, true, 512, \JSON_THROW_ON_ERROR);

            if (!is_array($data) || !isset($data['installed']) || !is_array($data['installed'])) {
                return [];
            }

            /** @var array<int, array{name: string, version: string, latest: string, latest-status: string}> $installed */
            $installed = $data['installed'];

            return $installed;
        } catch (\JsonException) {
            return [];
        }
    }

    public function getNpmOutdated(): array
    {
        $output = shell_exec('npm outdated --json 2>/dev/null');

        if (null === $output || false === $output || '' === $output) {
            return [];
        }

        try {
            $data = json_decode($output, true, 512, \JSON_THROW_ON_ERROR);

            if (!is_array($data)) {
                return [];
            }

            /** @var array<string, array{current: string, wanted: string, latest: string}> $npmData */
            $npmData = $data;

            return $npmData;
        } catch (\JsonException) {
            return [];
        }
    }

    public function getDependabotPRs(): array
    {
        $output = shell_exec('gh pr list --state open --json number,title,author 2>/dev/null');

        if (null === $output || false === $output || '' === $output) {
            return [];
        }

        try {
            $prs = json_decode($output, true, 512, \JSON_THROW_ON_ERROR);

            if (!is_array($prs)) {
                return [];
            }

            $dependabotPRs = [];

            foreach ($prs as $pr) {
                if (!is_array($pr)) {
                    continue;
                }

                if (!isset($pr['author']) || !is_array($pr['author']) || !isset($pr['author']['login'])) {
                    continue;
                }

                if ('app/dependabot' === $pr['author']['login']) {
                    if (!isset($pr['number'], $pr['title']) || !is_int($pr['number']) || !is_string($pr['title'])) {
                        continue;
                    }

                    $dependabotPRs[] = [
                        'number' => $pr['number'],
                        'title' => $pr['title'],
                    ];
                }
            }

            return $dependabotPRs;
        } catch (\JsonException) {
            return [];
        }
    }

    public function getComposerWhyNot(string $package, string $version): string
    {
        $output = shell_exec(sprintf('composer why-not %s %s 2>&1', escapeshellarg($package), escapeshellarg($version)));

        if (null === $output || false === $output || '' === $output) {
            return 'Impossible de déterminer la contrainte bloquante';
        }

        return $output;
    }
}
