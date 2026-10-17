<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Webhook\ReceivedWebhookStore;
use App\Entity\Webhook\ReceivedWebhook;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;

/**
 * US-029: the webhooks the outbox delivered to the demo receiver, in the back office (Integrations menu). Read-only; the
 * payloads are rendered escaped, whatever a visitor typed in an order.
 */
final readonly class ReceivedWebhooksController
{
    public function __construct(
        private Environment $twig,
        private ReceivedWebhookStore $store,
    ) {
    }

    #[Route('/%sylius_admin.path_name%/demo/webhooks', name: 'app_demo_admin_received_webhooks', methods: ['GET'])]
    public function index(): Response
    {
        return new Response($this->twig->render('admin/demo/webhooks/index.html.twig', ['webhooks' => $this->store->latest()]));
    }

    #[Route('/%sylius_admin.path_name%/demo/webhooks/{id}', name: 'app_demo_admin_received_webhook', requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET'])]
    public function show(string $id): Response
    {
        $webhook = $this->store->find(Uuid::fromString($id)) ?? throw new NotFoundHttpException();

        return new Response($this->twig->render('admin/demo/webhooks/show.html.twig', [
            'webhook' => $webhook,
            'payload' => $this->prettyPayload($webhook),
        ]));
    }

    /** A truncated payload is not valid JSON: it is shown as stored. */
    private function prettyPayload(ReceivedWebhook $webhook): string
    {
        $decoded = $webhook->isPayloadTruncated() ? null : json_decode($webhook->getPayload(), true);
        $pretty = null === $decoded ? false : json_encode($decoded, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);

        return false === $pretty ? $webhook->getPayload() : $pretty;
    }
}
