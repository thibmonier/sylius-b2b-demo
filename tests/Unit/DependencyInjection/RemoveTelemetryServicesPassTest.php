<?php

declare(strict_types=1);

namespace App\Tests\Unit\DependencyInjection;

use App\DependencyInjection\Compiler\RemoveTelemetryServicesPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RemoveTelemetryServicesPassTest extends TestCase
{
    public function testItRemovesTheTelemetryServicesInProdAndKeepsTheOthers(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        $container->register('sylius.telemetry.send_manager', \stdClass::class);
        $container->register('sylius.telemetry.listener', \stdClass::class)->addTag('kernel.event_listener', ['event' => 'kernel.terminate']);
        $container->register('sylius.telemetry.listener.notice', \stdClass::class);
        $container->setAlias('sylius.telemetry.sender_alias', 'sylius.telemetry.send_manager');
        $container->register('sylius.listener.telemetry_index_schema', \stdClass::class);
        $container->register('sylius.repository.order', \stdClass::class);

        (new RemoveTelemetryServicesPass())->process($container);

        self::assertFalse($container->hasDefinition('sylius.telemetry.send_manager'));
        self::assertFalse($container->hasDefinition('sylius.telemetry.listener'));
        self::assertFalse($container->hasDefinition('sylius.telemetry.listener.notice'));
        self::assertFalse($container->hasAlias('sylius.telemetry.sender_alias'));
        self::assertFalse($container->hasDefinition('sylius.listener.telemetry_index_schema'));
        self::assertTrue($container->hasDefinition('sylius.repository.order'));
    }

    public function testTheSchemaListenerStaysInTestToKeepSchemaDiffsStable(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');
        $container->register('sylius.listener.telemetry_index_schema', \stdClass::class);

        (new RemoveTelemetryServicesPass())->process($container);

        self::assertTrue($container->hasDefinition('sylius.listener.telemetry_index_schema'));
    }
}
