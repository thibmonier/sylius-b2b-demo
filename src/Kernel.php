<?php

declare(strict_types=1);

namespace App;

use App\DependencyInjection\Compiler\RemoveTelemetryServicesPass;
use App\DependencyInjection\Compiler\RestrictPriceRoutesPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        // Positive priority: before the Doctrine listeners pass, which would reference the schema listener.
        $container->addCompilerPass(new RemoveTelemetryServicesPass(schemaListenerOnly: true), PassConfig::TYPE_BEFORE_OPTIMIZATION, 100);
        // Negative priority: after the sylius/telemetry pass of the plugins, which reloads the services.
        $container->addCompilerPass(new RemoveTelemetryServicesPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -100);
        // The parameters are set by the extension of the channel access plugin: read them after it has loaded.
        $container->addCompilerPass(new RestrictPriceRoutesPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}
