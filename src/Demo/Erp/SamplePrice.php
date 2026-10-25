<?php

declare(strict_types=1);

namespace App\Demo\Erp;

final readonly class SamplePrice
{
    public function __construct(public string $variantCode, public string $channelCode, public string $currency, public int $priceMinor)
    {
    }
}
