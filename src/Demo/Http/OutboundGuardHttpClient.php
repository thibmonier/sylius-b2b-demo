<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * No real outbound call from the public demo (US-036): in the demo mode an HTTP request to a host that is
 * not loopback, on the Railway private network or in DEMO_OUTBOUND_ALLOWED_HOSTS is refused before any
 * connection, and redirects are not followed. Applied to the default HTTP client, to its transport (the scoped clients of the MCP server are built on it) and to the webhook
 * transport.
 */
#[AsDecorator('http_client')]
final class OutboundGuardHttpClient implements HttpClientInterface, ResetInterface
{
    public function __construct(
        #[AutowireDecorated]
        private HttpClientInterface $client,
        #[Autowire(param: 'demo.mode')]
        private readonly bool $demoMode,
        #[Autowire(param: 'demo.outbound.allowed_hosts')]
        private readonly string $allowedHosts,
    ) {
    }

    /** @param array<string, mixed> $options */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        if ($this->demoMode) {
            $this->assertAllowed($url, $options);
            // The guard only sees the first URL: a redirect would take the connection to a host nobody checked. The
            // hosts of the demo (shop API, mail, webhooks, icons) never redirect, so a 3xx is returned as it is.
            $options['max_redirects'] = 0;
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

    /** @param array<string, mixed> $options */
    private function assertAllowed(string $url, array $options): void
    {
        $base = $options['base_uri'] ?? null;
        $host = parse_url($url, \PHP_URL_HOST);
        if (!\is_string($host) && \is_string($base)) {
            $host = parse_url($base, \PHP_URL_HOST);
        }
        if (!\is_string($host) || !HostAllowList::fromCsv($this->allowedHosts)->allows($host)) {
            throw new OutboundRequestRefused('Outbound HTTP requests are disabled on the public demo.');
        }
    }
}
