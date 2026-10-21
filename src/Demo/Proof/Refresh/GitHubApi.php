<?php

declare(strict_types=1);

namespace App\Demo\Proof\Refresh;

interface GitHubApi
{
    /**
     * @return array<array-key, mixed> the decoded answer of a REST path such as "repos/owner/name"
     *
     * @throws SourceUnavailable
     */
    public function json(string $path): array;

    /**
     * @return string|null the raw content of a file of a public repository, null when it does not exist (404)
     *
     * @throws SourceUnavailable
     */
    public function file(string $repository, string $path, string $ref): ?string;
}
