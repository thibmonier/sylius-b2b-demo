<?php

declare(strict_types=1);

namespace App\Demo\Erp;

interface ImportedBatches
{
    /** @return list<ImportedBatchRow> */
    public function all(): array;
}
