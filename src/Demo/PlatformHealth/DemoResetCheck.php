<?php

declare(strict_types=1);

namespace App\Demo\PlatformHealth;

use App\Demo\Command\ResetStatus;
use Symfony\Component\Translation\TranslatableMessage;
use Tmonier\SyliusPlatformHealthPlugin\Health\HealthCategory;
use Tmonier\SyliusPlatformHealthPlugin\Health\HealthCheckInterface;
use Tmonier\SyliusPlatformHealthPlugin\Health\HealthResult;

/**
 * US-321: the last nightly reset (US-033, US-049) as a probe of the platform health plugin. Critical while the last
 * reset failed, so that an uptime monitor on /healthz raises the alert; a reset in progress is not a failure.
 */
final readonly class DemoResetCheck implements HealthCheckInterface
{
    public function __construct(
        private ResetStatus $resetStatus,
    ) {
    }

    public function identifier(): string
    {
        return 'demo.reset';
    }

    public function category(): HealthCategory
    {
        return HealthCategory::Environment;
    }

    public function run(): HealthResult
    {
        // The demo still serves its previous data, but the alert must reach the uptime monitor (US-033).
        if ($this->resetStatus->hasFailed()) {
            return HealthResult::critical(new TranslatableMessage('demo_health.reset.critical', [], 'demo_health'));
        }

        return HealthResult::ok(new TranslatableMessage('demo_health.reset.ok', [], 'demo_health'));
    }
}
