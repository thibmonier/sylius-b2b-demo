<?php

declare(strict_types=1);

namespace App\Demo\Webhook;

use App\Entity\Webhook\ReceivedWebhook;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/** Keeps the last webhooks the demo receiver accepted: bounded, and one row per webhook-id (a replay changes nothing). */
final readonly class ReceivedWebhookStore
{
    public const MAX_ROWS = 200;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /** @return bool false when this webhook-id was already stored */
    public function record(ReceivedWebhook $webhook): bool
    {
        if (null !== $this->entityManager->getRepository(ReceivedWebhook::class)->findOneBy(['webhookId' => $webhook->getWebhookId()])) {
            return false;
        }

        try {
            $this->entityManager->persist($webhook);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return false;
        }
        $this->prune();

        return true;
    }

    /** @return list<ReceivedWebhook> */
    public function latest(int $limit = self::MAX_ROWS): array
    {
        return $this->entityManager->getRepository(ReceivedWebhook::class)->findBy([], ['receivedAt' => 'DESC', 'id' => 'DESC'], $limit);
    }

    public function find(Uuid $id): ?ReceivedWebhook
    {
        return $this->entityManager->find(ReceivedWebhook::class, $id);
    }

    private function prune(): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'DELETE FROM app_received_webhook WHERE received_at < (SELECT received_at FROM (SELECT received_at FROM app_received_webhook ORDER BY received_at DESC LIMIT 1 OFFSET ' . (self::MAX_ROWS - 1) . ') AS kept)',
        );
    }
}
