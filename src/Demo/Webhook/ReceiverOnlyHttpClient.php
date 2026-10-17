<?php

declare(strict_types=1);

namespace App\Demo\Webhook;

use App\Demo\Http\OutboundRequestRefused;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * US-029: emission-side check, behind the validator of the endpoints. In the demo mode the webhook transport only
 * talks to the receiver of the demo, even if a row of the database was edited by hand (the generic outbound guard,
 * which also decorates this transport, lets every allowed host through).
 */
final class ReceiverOnlyHttpClient implements HttpClientInterface, ResetInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private readonly DemoWebhookTarget $target,
        #[Autowire(param: 'demo.mode')]
        private readonly bool $demoMode,
    ) {
    }

    /** @param array<string, mixed> $options */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        if ($this->demoMode && !$this->target->accepts($url)) {
            throw new OutboundRequestRefused('Webhooks of the public demo are delivered to the demo receiver only.');
        }

        return $this->client->request($method, $url, $options);
    }

    public function stream(ResponseInterface|iterable $responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->client->stream($responses, $timeout);
    }

    /** @param array<string, mixed> $options */
    public function withOptions(array $options): static
    {
        $clone = clone $this;
        $clone->client = $this->client->withOptions($options);

        return $clone;
    }

    public function reset(): void
    {
        if ($this->client instanceof ResetInterface) {
            $this->client->reset();
        }
    }
}
