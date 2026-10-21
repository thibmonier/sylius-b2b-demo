<?php

declare(strict_types=1);

namespace App\Demo\Journey;

/** One step of a guided journey (US-105): a shop route to look at and, optionally, a scenario to prepare for it. */
final readonly class JourneyStep
{
    /**
     * @param array<string, string> $routeParameters
     * @param array<string, string> $scenarioOptions
     */
    public function __construct(
        public int $number,
        public string $route,
        public array $routeParameters = [],
        public ?string $scenario = null,
        public array $scenarioOptions = [],
    ) {
    }

    public function hasScenario(): bool
    {
        return null !== $this->scenario;
    }
}
