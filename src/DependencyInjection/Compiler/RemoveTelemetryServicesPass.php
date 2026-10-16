<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Removes the Sylius telemetry services (ADR-0012): the public demo must make no outbound call.
 *
 * The `sylius/telemetry` pass, registered by the official plugins (wishlist, refund, invoicing), reloads
 * the core telemetry services outside the dev and test environments, whatever `sylius_core.telemetry` says.
 * This pass runs after it and drops every `sylius.telemetry.*` definition (sender, listeners, collectors).
 * Outside dev and test it also drops `sylius.listener.telemetry_index_schema` (no network, but a telemetry
 * service): nobody generates schema diffs in prod. In dev and test it stays, so that schema tools do not
 * propose dropping the index it keeps. That listener is tagged for Doctrine, so it must go before the
 * Doctrine listeners pass: the Kernel registers this pass twice, early (schema listener only) and late.
 */
final class RemoveTelemetryServicesPass implements CompilerPassInterface
{
    private const PREFIX = 'sylius.telemetry.';

    private const SCHEMA_LISTENER = 'sylius.listener.telemetry_index_schema';

    public function __construct(private readonly bool $schemaListenerOnly = false)
    {
    }

    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(self::SCHEMA_LISTENER) && !$this->isDevOrTest($container)) {
            $container->removeDefinition(self::SCHEMA_LISTENER);
        }

        if ($this->schemaListenerOnly) {
            return;
        }

        foreach (array_keys($container->getDefinitions()) as $id) {
            if (str_starts_with((string) $id, self::PREFIX)) {
                $container->removeDefinition((string) $id);
            }
        }

        foreach (array_keys($container->getAliases()) as $id) {
            if (str_starts_with((string) $id, self::PREFIX)) {
                $container->removeAlias((string) $id);
            }
        }
    }

    private function isDevOrTest(ContainerBuilder $container): bool
    {
        $env = $container->getParameter('kernel.environment');

        return \is_string($env) && (str_starts_with($env, 'dev') || str_starts_with($env, 'test'));
    }
}
