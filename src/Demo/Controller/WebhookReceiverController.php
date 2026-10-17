<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Webhook\DemoWebhookTarget;
use App\Demo\Webhook\ReceivedWebhookStore;
use App\Entity\Webhook\ReceivedWebhook;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Tmonier\SyliusWebhookOutboxPlugin\Repository\WebhookEndpointRepositoryInterface;
use Tmonier\SyliusWebhookOutboxPlugin\Signature\WebhookSigner;

/**
 * US-029: the receiver the outbox of the demo delivers to, so that a visitor can see the integration without any
 * outbound call. It answers 2xx directly (the outbound guard does not follow redirects) and is exempted from the private
 * gate by its exact path (DemoGateListener): the Standard Webhooks signature, checked against the secret of the demo
 * endpoint, takes the place of the authentication. A webhook that does not verify is refused and not stored.
 */
final readonly class WebhookReceiverController
{
    private const MAX_BODY_BYTES = 262144;

    public function __construct(
        private WebhookEndpointRepositoryInterface $endpoints,
        #[Autowire(service: 'tmonier_webhook.signer')]
        private WebhookSigner $signer,
        private DemoWebhookTarget $target,
        private ReceivedWebhookStore $store,
        private RateLimiterFactory $demoWebhookReceiverLimiter,
    ) {
    }

    #[Route(DemoWebhookTarget::PATH, name: 'app_demo_webhook_receive', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->demoWebhookReceiverLimiter->create($request->getClientIp() ?? 'unknown')->consume()->isAccepted()) {
            return $this->answer(Response::HTTP_TOO_MANY_REQUESTS, 'rate_limited', ['Retry-After' => '60']);
        }
        if ((int) $request->headers->get('Content-Length', '0') > self::MAX_BODY_BYTES) {
            return $this->answer(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, 'payload_too_large');
        }
        $body = $request->getContent();
        if (\strlen($body) > self::MAX_BODY_BYTES) {
            return $this->answer(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, 'payload_too_large');
        }

        $endpoint = $this->endpoints->findOneBy(['url' => $this->target->url(), 'enabled' => true]);
        if (null === $endpoint) {
            return $this->answer(Response::HTTP_NOT_FOUND, 'no_endpoint');
        }

        $id = (string) $request->headers->get('webhook-id');
        if ('' === $id || \strlen($id) > 255 || !$this->isSigned($request, $id, $body, (string) $endpoint->getSecret())) {
            return $this->answer(Response::HTTP_UNAUTHORIZED, 'invalid_signature');
        }

        $type = $this->eventType($body);
        if (null === $type) {
            return $this->answer(Response::HTTP_BAD_REQUEST, 'invalid_payload');
        }

        return $this->answer(Response::HTTP_OK, $this->store->record(new ReceivedWebhook($id, $type, $body, true)) ? 'received' : 'duplicate');
    }

    private function isSigned(Request $request, string $id, string $body, #[\SensitiveParameter] string $secret): bool
    {
        try {
            return $this->signer->verify(
                $id,
                (string) $request->headers->get('webhook-timestamp'),
                $body,
                (string) $request->headers->get('webhook-signature'),
                $secret,
                time(),
            );
        } catch (\InvalidArgumentException) {
            // The secret of the endpoint is not a whsec_ secret: nothing can be verified.
            return false;
        }
    }

    private function eventType(string $body): ?string
    {
        $envelope = json_decode($body, true);
        $type = \is_array($envelope) ? ($envelope['type'] ?? null) : null;

        return \is_string($type) && '' !== $type && \strlen($type) <= 100 ? $type : null;
    }

    /** @param array<string, string> $headers */
    private function answer(int $status, string $result, array $headers = []): Response
    {
        return new JsonResponse(['status' => $result], $status, $headers + ['Cache-Control' => 'no-store']);
    }
}
