<?php

declare(strict_types=1);

namespace App\Demo\Erp;

final readonly class SampleStock
{
    public function __construct(public string $variantCode, public int $onHand)
    {
    }
}
