<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Http\DemoConfigurationGuard;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\DependencyFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Healthcheck of the Railway service and of the image (US-002, US-032): 200 when the database answers,
 * every migration is applied and the demo is configured safely; 503 otherwise, with a body that never
 * names a host, a table or a trace. Never cached, no session, outside the private gate. Served on
 * /health (Railway) and /healthz (image, maintenance mode).
 */
final readonly class HealthController
{
    public function __construct(
        private Connection $connection,
        #[Autowire(service: 'doctrine.migrations.dependency_factory')]
        private DependencyFactory $migrations,
        private DemoConfigurationGuard $guard,
    ) {
    }

    #[Route('/health', name: 'app_demo_health', methods: ['GET'])]
    #[Route('/healthz', name: 'app_demo_healthz', methods: ['GET'])]
    public function __invoke(): Response
    {
        try {
            $this->connection->executeQuery('SELECT 1');
        } catch (\Throwable) {
            return $this->plain('database unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }
        if ($this->hasPendingMigrations()) {
            return $this->plain('migrations pending', Response::HTTP_SERVICE_UNAVAILABLE);
        }
        if ([] !== $this->guard->violations()) {
            return $this->plain('misconfigured', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $this->plain('ok', Response::HTTP_OK);
    }

    private function hasPendingMigrations(): bool
    {
        try {
            return \count($this->migrations->getMigrationStatusCalculator()->getNewMigrations()) > 0;
        } catch (\Throwable) {
            return true;
        }
    }

    private function plain(string $body, int $status): Response
    {
        return new Response($body, $status, ['Content-Type' => 'text/plain', 'Cache-Control' => 'no-store']);
    }
}
