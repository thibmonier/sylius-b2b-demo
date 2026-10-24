<?php

declare(strict_types=1);

namespace App\Demo\Erp;

/** A file of orders written by the export plugin, as its journal remembers it. */
final readonly class OutboundBatch
{
    public function __construct(public string $file, public \DateTimeImmutable $at, public int $orders)
    {
    }
}
