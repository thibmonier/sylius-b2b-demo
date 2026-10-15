<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Healthcheck of the Railway service and of the image (US-002): 200 when the database answers,
 * 503 otherwise. Never cached, no session.
 */
final readonly class HealthController
{
    public function __construct(private Connection $connection)
    {
    }

    #[Route('/healthz', name: 'app_demo_healthz', methods: ['GET'])]
    public function __invoke(): Response
    {
        try {
            $this->connection->executeQuery('SELECT 1');
        } catch (\Throwable) {
            return $this->plain('database unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $this->plain('ok', Response::HTTP_OK);
    }

    private function plain(string $body, int $status): Response
    {
        return new Response($body, $status, ['Content-Type' => 'text/plain', 'Cache-Control' => 'no-store']);
    }
}
