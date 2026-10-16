<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

/**
 * Proof that a request comes from the demo itself (the PunchOut simulator calling the shop on loopback):
 * an HMAC of the kernel secret carried in a header. It lets that call through the private gate and to
 * the PunchOut endpoint, which refuse every other client.
 */
final readonly class InternalRequestToken
{
    public const HEADER = 'X-Demo-Internal';

    public function __construct(
        #[Autowire('%kernel.secret%')]
        #[\SensitiveParameter]
        private string $secret,
    ) {
    }

    public function value(): string
    {
        return hash_hmac('sha256', 'demo-internal-request', $this->secret);
    }

    public function isValid(Request $request): bool
    {
        $given = $request->headers->get(self::HEADER);

        return \is_string($given) && hash_equals($this->value(), $given);
    }
}
