<?php

declare(strict_types=1);

namespace App\Demo\Erp;

interface OutboundBatches
{
    /** @return list<OutboundBatch> */
    public function all(): array;
}
