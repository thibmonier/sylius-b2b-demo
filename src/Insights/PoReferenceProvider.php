<?php

declare(strict_types=1);

namespace App\Insights;

use Tmonier\SyliusCompanyInsightsPlugin\Breakdown\OrderReferenceProviderInterface;

/** The "PO reference" column of the CSV export of the purchasing dashboard (US-152): the purchase order number of the order. */
final readonly class PoReferenceProvider implements OrderReferenceProviderInterface
{
    public function __construct(private OrderColumns $columns)
    {
    }

    public function references(array $orderIds): array
    {
        return $this->columns->read('po_reference', $orderIds);
    }
}
