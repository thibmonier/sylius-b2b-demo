<?php

declare(strict_types=1);

namespace App\Demo\Stack;

/**
 * Source of the two facts that gate a PHP major upgrade (ADR-0021): whether the runtime image exists
 * and whether any locked package still excludes the target version.
 */
interface PhpUpgradeWatchSource
{
    /**
     * Number of published dunglas/frankenphp tags whose name contains $imageTag (e.g. "php8.6").
     * Zero means the runtime does not exist yet, which is the blocking condition.
     */
    public function frankenPhpTagCount(string $imageTag): int;

    /**
     * Locked packages whose "php" requirement excludes $phpVersion.
     *
     * @return array<string, string> package name => the constraint that excludes it
     */
    public function packagesExcluding(string $phpVersion): array;
}
