<?php

declare(strict_types=1);

namespace App\Demo\Journey;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

/** Runs the scenario of a step ("Je lance ce scénario", US-105) with the preparer registered under its name. */
final readonly class JourneyLauncher
{
    public function __construct(
        #[AutowireLocator(ScenarioPreparer::TAG, indexAttribute: 'key')]
        private ContainerInterface $preparers,
    ) {
    }

    public function knows(string $scenario): bool
    {
        return $this->preparers->has($scenario);
    }

    /** @throws ScenarioPreparationFailed */
    public function launch(JourneyStep $step): void
    {
        if (null === $step->scenario || !$this->knows($step->scenario)) {
            throw new ScenarioPreparationFailed(\sprintf('Step %d has no known scenario.', $step->number));
        }
        $preparer = $this->preparers->get($step->scenario);
        \assert($preparer instanceof ScenarioPreparer);
        $preparer->prepare($step->scenarioOptions);
    }
}
