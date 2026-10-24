<?php

declare(strict_types=1);

namespace App\Demo\Erp;

use Tmonier\SyliusWebhookOutboxPlugin\Entity\OutboxEventInterface;

/** The `order.placed` events of the demo, most recent first. */
interface RecentPlacedOrders
{
    /** @return list<OutboxEventInterface> */
    public function latest(int $limit): array;
}
