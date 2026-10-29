<?php

declare(strict_types=1);

namespace App\Demo\Stack;

/**
 * US-239: source of outdated packages data.
 * Implementations fetch data from composer, npm, Docker registries, and GitHub.
 */
interface OutdatedSource
{
    /** @return array<int, array{name: string, version: string, latest: string, latest-status: string}> */
    public function getComposerOutdated(): array;

    /** @return array<string, array{current: string, wanted: string, latest: string}> */
    public function getNpmOutdated(): array;

    /** @return array<int, array{number: int, title: string}> */
    public function getDependabotPRs(): array;

    public function getComposerWhyNot(string $package, string $version): string;
}
