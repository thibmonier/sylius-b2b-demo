<?php

declare(strict_types=1);

namespace App\Demo\Webhook;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * US-029: the one URL a webhook endpoint may have in the demo, the receiver of this very container (loopback, port of
 * the web server, exact path). Everything else is refused, whatever the allow-list of the outbound guard says.
 */
final readonly class DemoWebhookTarget
{
    public const PATH = '/demo/webhooks/receive';

    public function __construct(
        #[Autowire(env: 'int:PORT')]
        private int $port,
    ) {
    }

    public function url(): string
    {
        return \sprintf('http://127.0.0.1:%d%s', $this->port, self::PATH);
    }

    /** Strict equality: no other loopback spelling, port, query, fragment or credential is the receiver. */
    public function accepts(string $url): bool
    {
        return $url === $this->url();
    }
}
