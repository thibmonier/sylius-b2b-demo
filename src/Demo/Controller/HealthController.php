<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Command\ResetStatus;
use App\Demo\Http\DemoConfigurationGuard;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\DependencyFactory;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Healthcheck of the Railway service and of the image (US-002, US-032): 200 when the database answers,
 * every migration is applied, the demo is configured safely and the last nightly reset did not fail; 503 otherwise, with a body that never
 * names a host, a table or a trace. Never cached by a browser or a proxy, no session, outside the private gate and
 * limited per visitor IP (RateLimitSubscriber, US-040). The database and migration verdict is kept a few seconds,
 * so a burst never costs a query per call. Served on /health (Railway) and /healthz (image, maintenance mode).
 */
final readonly class HealthController
{
    private const VERDICT_KEY = 'demo_health_verdict';

    private const VERDICT_TTL = 5;

    public function __construct(
        private Connection $connection,
        #[Autowire(service: 'doctrine.migrations.dependency_factory')]
        private DependencyFactory $migrations,
        private DemoConfigurationGuard $guard,
        private ResetStatus $resetStatus,
        #[Autowire(service: 'cache.app')]
        private CacheItemPoolInterface $cache,
    ) {
    }

    #[Route('/health', name: 'app_demo_health', methods: ['GET'])]
    #[Route('/healthz', name: 'app_demo_healthz', methods: ['GET'])]
    public function __invoke(): Response
    {
        $failure = $this->storageFailure();
        if (null !== $failure) {
            return $this->plain($failure, Response::HTTP_SERVICE_UNAVAILABLE);
        }
        if ([] !== $this->guard->violations()) {
            return $this->plain('misconfigured', Response::HTTP_SERVICE_UNAVAILABLE);
        }
        // The demo still serves its previous data, but the alert must reach the uptime monitor (US-033).
        if ($this->resetStatus->hasFailed()) {
            return $this->plain('reset failed', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $this->plain('ok', Response::HTTP_OK);
    }

    /** The reason the storage is unhealthy, or null; asked to the database at most once per VERDICT_TTL seconds. */
    private function storageFailure(): ?string
    {
        $item = $this->cache->getItem(self::VERDICT_KEY);
        if ($item->isHit()) {
            $cached = $item->get();

            return \is_string($cached) && '' !== $cached ? $cached : null;
        }

        $failure = $this->probeStorage();
        $this->cache->save($item->set($failure ?? '')->expiresAfter(self::VERDICT_TTL));

        return $failure;
    }

    private function probeStorage(): ?string
    {
        try {
            $this->connection->executeQuery('SELECT 1');
        } catch (\Throwable) {
            return 'database unavailable';
        }

        return $this->hasPendingMigrations() ? 'migrations pending' : null;
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
