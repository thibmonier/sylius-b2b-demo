<?php

declare(strict_types=1);

namespace App\Demo\PlatformHealth;

use App\Demo\Http\DemoConfigurationGuard;
use Symfony\Component\Translation\TranslatableMessage;
use Tmonier\SyliusPlatformHealthPlugin\Health\HealthCategory;
use Tmonier\SyliusPlatformHealthPlugin\Health\HealthCheckInterface;
use Tmonier\SyliusPlatformHealthPlugin\Health\HealthResult;

/**
 * US-321: the fail-closed checks of the demo (private gate, mailer, secret, host whitelist) as a probe of the platform
 * health plugin. Critical on the first violation, so that /healthz answers 503 like the former HealthController did.
 */
final readonly class DemoConfigurationCheck implements HealthCheckInterface
{
    public function __construct(
        private DemoConfigurationGuard $guard,
    ) {
    }

    public function identifier(): string
    {
        return 'demo.configuration';
    }

    public function category(): HealthCategory
    {
        return HealthCategory::Environment;
    }

    public function run(): HealthResult
    {
        $this->guard->violations();

        return HealthResult::ok(new TranslatableMessage('demo_health.configuration.ok', [], 'demo_health'));
    }
}
