<?php

declare(strict_types=1);

namespace App\Demo\Erp;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusWebhookOutboxPlugin\Entity\OutboxEventInterface;

/** Reads the outbox of the webhook plugin as it is (read only, nothing is marked exported). */
final readonly class DoctrineRecentPlacedOrders implements RecentPlacedOrders
{
    /** @param class-string<OutboxEventInterface> $outboxEventClass */
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire(param: 'tmonier_webhook.model.outbox_event.class')]
        private string $outboxEventClass,
    ) {
    }

    public function latest(int $limit): array
    {
        /** @var list<OutboxEventInterface> $events */
        $events = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($this->outboxEventClass, 'e')
            ->andWhere('e.type = :type')
            ->andWhere('e.anonymizedAt IS NULL')
            ->orderBy('e.occurredAt', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->setMaxResults($limit)
            ->setParameter('type', 'order.placed')
            ->getQuery()
            ->getResult()
        ;

        return $events;
    }
}
