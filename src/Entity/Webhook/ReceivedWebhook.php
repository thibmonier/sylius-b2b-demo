<?php

declare(strict_types=1);

namespace App\Entity\Webhook;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * US-029: a webhook the demo receiver accepted. Only verified deliveries are stored; the payload is truncated and the
 * table is bounded (see ReceivedWebhookStore) and emptied by the nightly reset.
 */
#[ORM\Entity]
#[ORM\Table(name: 'app_received_webhook')]
#[ORM\Index(name: 'idx_app_received_webhook_received_at', columns: ['received_at'])]
class ReceivedWebhook
{
    public const MAX_PAYLOAD_BYTES = 8192;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(name: 'received_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $receivedAt;

    #[ORM\Column(type: 'text')]
    private string $payload;

    #[ORM\Column(name: 'payload_truncated', type: 'boolean')]
    private bool $payloadTruncated;

    public function __construct(
        #[ORM\Column(name: 'webhook_id', type: 'string', length: 255, unique: true)]
        private string $webhookId,
        #[ORM\Column(name: 'event_type', type: 'string', length: 100)]
        private string $eventType,
        string $payload,
        #[ORM\Column(name: 'signature_verified', type: 'boolean')]
        private bool $signatureVerified,
    ) {
        $this->id = Uuid::v7();
        $this->receivedAt = new \DateTimeImmutable();
        $this->payloadTruncated = \strlen($payload) > self::MAX_PAYLOAD_BYTES;
        $this->payload = $this->payloadTruncated ? mb_strcut($payload, 0, self::MAX_PAYLOAD_BYTES) : $payload;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getWebhookId(): string
    {
        return $this->webhookId;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getReceivedAt(): \DateTimeImmutable
    {
        return $this->receivedAt;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function isPayloadTruncated(): bool
    {
        return $this->payloadTruncated;
    }

    public function isSignatureVerified(): bool
    {
        return $this->signatureVerified;
    }
}
