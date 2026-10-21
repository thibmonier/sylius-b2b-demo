<?php

declare(strict_types=1);

namespace App\Demo\Journey;

/** A guided journey (US-105): ordered steps, the demo account that shows them best and an estimated duration. */
final readonly class Journey
{
    /** @param list<JourneyStep> $steps */
    public function __construct(
        public string $id,
        public string $persona,
        public string $accountEmail,
        public int $minutes,
        public array $steps,
    ) {
        if ([] === $steps) {
            throw new \InvalidArgumentException(\sprintf('Journey "%s" has no step.', $id));
        }
    }

    public function stepCount(): int
    {
        return \count($this->steps);
    }

    /** The number of the exit screen, shown after the last step. */
    public function exitNumber(): int
    {
        return $this->stepCount() + 1;
    }

    public function step(int $number): ?JourneyStep
    {
        return $number >= 1 && $number <= $this->stepCount() ? $this->steps[$number - 1] : null;
    }

    public function isExit(int $number): bool
    {
        return $number === $this->exitNumber();
    }
}
