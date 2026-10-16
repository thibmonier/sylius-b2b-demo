<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

use Symfony\Component\Clock\ClockInterface;

/** Builds the PunchOutSetupRequest (operation "create") the procurement tool sends to open a shopping session. */
final readonly class SetupRequestBuilder
{
    public function __construct(private ClockInterface $clock)
    {
    }

    public function build(BuyerCredentials $credentials, string $buyerCookie, string $returnUrl): string
    {
        $now = \DateTimeImmutable::createFromInterface($this->clock->now());
        $envelope = new CxmlEnvelope($credentials, self::payloadId(), $now);

        $setup = $envelope->append($envelope->request, 'PunchOutSetupRequest', null, ['operation' => 'create']);
        $envelope->append($setup, 'BuyerCookie', $buyerCookie);
        $envelope->append($envelope->append($setup, 'BrowserFormPost'), 'URL', $returnUrl);

        return $envelope->toXml();
    }

    public static function payloadId(): string
    {
        return bin2hex(random_bytes(12)) . '@acme-simulator.example';
    }
}
