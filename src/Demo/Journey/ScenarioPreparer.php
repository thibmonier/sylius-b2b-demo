<?php

declare(strict_types=1);

namespace App\Demo\Journey;

/**
 * Prepares the data a journey step shows (US-105): fills the cart, starts a quote request. Registered by the service tag
 * `app.demo.scenario_preparer` with a `key` (the `scenario` name used in config/demo/journeys.yaml).
 */
interface ScenarioPreparer
{
    public const TAG = 'app.demo.scenario_preparer';

    /**
     * Acts for the signed-in visitor, with the options of the step. Only touches what the visitor can reach themselves.
     *
     * @param array<string, string> $options
     *
     * @throws ScenarioPreparationFailed when there is nothing to prepare (no company, no such list, empty list)
     */
    public function prepare(array $options): void;
}
