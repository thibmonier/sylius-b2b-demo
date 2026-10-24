<?php

declare(strict_types=1);

namespace App\Demo\Erp;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusWebhookOutboxPlugin\Entity\OutboxEventInterface;

final readonly class DoctrineExportedOrderChannels implements ExportedOrderChannels
{
    /** @param class-string<OutboxEventInterface> $outboxEventClass */
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire(param: 'tmonier_webhook.model.outbox_event.class')]
        private string $outboxEventClass,
    ) {
    }

    public function channelsOf(array $eventIds): array
    {
        if ([] === $eventIds) {
            return [];
        }

        /** @var list<array{id: string, channelCode: string|null}> $rows */
        $rows = $this->entityManager->createQueryBuilder()
            ->select('e.id', 'e.channelCode')
            ->from($this->outboxEventClass, 'e')
            ->andWhere('e.id IN (:ids)')
            ->setParameter('ids', $eventIds, ArrayParameterType::STRING)
            ->getQuery()
            ->getArrayResult()
        ;

        $channels = [];
        foreach ($rows as $row) {
            if (null !== $row['channelCode']) {
                $channels[$row['id']] = $row['channelCode'];
            }
        }

        return $channels;
    }
}
