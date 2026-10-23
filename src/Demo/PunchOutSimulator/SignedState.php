<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Stateless, signed and expiring state of the simulator. The shop logs the shopper out when the cart is
 * sent back, so nothing is kept in the HTTP session: the BuyerCookie and the cart shown to the visitor
 * carry an HMAC (kernel secret) and a creation date. A forged or expired value is refused.
 */
final readonly class SignedState
{
    public const LIFETIME = 3600;

    public function __construct(
        #[Autowire(param: 'kernel.secret')]
        #[\SensitiveParameter]
        private string $secret,
        private ClockInterface $clock,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function sign(array $data): string
    {
        $payload = rtrim(strtr(base64_encode(json_encode($data + ['iat' => $this->clock->now()->getTimestamp()], \JSON_THROW_ON_ERROR)), '+/', '-_'), '=');

        return $payload . '.' . $this->mac($payload);
    }

    /** @return array<array-key, mixed>|null null when the signature is wrong or the value expired */
    public function verify(string $value): ?array
    {
        [$payload, $mac] = array_pad(explode('.', $value, 2), 2, '');
        if (!hash_equals($this->mac($payload), $mac)) {
            return null;
        }
        $data = json_decode((string) base64_decode(strtr($payload, '-_', '+/'), true), true);
        if (!is_array($data) || !is_int($data['iat'] ?? null) || $this->clock->now()->getTimestamp() - $data['iat'] > self::LIFETIME) {
            return null;
        }

        return $data;
    }

    private function mac(string $payload): string
    {
        return hash_hmac('sha256', 'punchout-simulator|' . $payload, $this->secret);
    }
}
