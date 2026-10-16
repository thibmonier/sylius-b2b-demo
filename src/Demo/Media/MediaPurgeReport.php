<?php

declare(strict_types=1);

namespace App\Demo\Media;

final readonly class MediaPurgeReport
{
    /** @param list<string> $failed relative paths that could not be removed */
    public function __construct(public int $deleted, public array $failed)
    {
    }

    public function hasFailures(): bool
    {
        return [] !== $this->failed;
    }
}
