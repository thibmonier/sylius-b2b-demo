<?php

declare(strict_types=1);

namespace App\Demo\Erp;

/** The channel of the orders of a file: the file has no channel column, the outbox event of each line (its webhook id) knows it. */
interface ExportedOrderChannels
{
    /**
     * @param list<string> $eventIds
     *
     * @return array<string, string> channel code by webhook id (the unknown ids are left out)
     */
    public function channelsOf(array $eventIds): array;
}
