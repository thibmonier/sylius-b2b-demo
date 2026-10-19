<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use App\Kernel;
use PHPUnit\Framework\TestCase;

/**
 * ADR-0012: the public demo makes no outbound telemetry call. The official plugins reload the Sylius
 * telemetry services outside the dev and test environments; the Kernel pass must have removed them in prod.
 */
final class TelemetryAbsentInProdTest extends TestCase
{
    public function testNoTelemetryListenerIsRegisteredInProd(): void
    {
        $kernel = new Kernel('prod', false);
        $kernel->boot();

        try {
            $container = $kernel->getContainer();
            $dispatcher = $container->get('event_dispatcher');
            self::assertInstanceOf(\Symfony\Component\EventDispatcher\EventDispatcherInterface::class, $dispatcher);

            $offenders = [];
            foreach ($dispatcher->getListeners() as $event => $listeners) {
                foreach ($listeners as $listener) {
                    $class = \is_array($listener) && \is_object($listener[0]) ? $listener[0]::class : '';
                    if (str_contains($class, 'Telemetry')) {
                        $offenders[] = $event . ' => ' . $class;
                    }
                }
            }

            self::assertSame([], $offenders, 'No telemetry listener must stay registered in prod.');
            self::assertFalse($container->has('sylius.telemetry.send_manager'));
        } finally {
            $kernel->shutdown();
        }
    }
}
