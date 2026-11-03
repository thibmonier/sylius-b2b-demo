<?php

declare(strict_types=1);

namespace App\Demo\Stack;

final readonly class PluginRepositoryStatus
{
    /**
     * @param ?bool                       $mainGreen    null when the default branch could not be read
     * @param list<DependabotPullRequest> $pullRequests open Dependabot pull requests
     */
    public function __construct(
        public string $repository,
        public ?bool $mainGreen,
        public array $pullRequests,
    ) {
    }
}
