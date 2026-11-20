<?php

declare(strict_types=1);

namespace App\Tests\Unit\Stack;

use App\Demo\Stack\PhpUpgradeWatchSource;

final readonly class FakePhpUpgradeWatchSource implements PhpUpgradeWatchSource
{
    /** @param array<string, string> $excluding */
    public function __construct(
        private int $tags,
        private array $excluding = [],
    ) {
    }

    public function frankenPhpTagCount(string $imageTag): int
    {
        return $this->tags;
    }

    public function packagesExcluding(string $phpVersion): array
    {
        return $this->excluding;
    }
}
