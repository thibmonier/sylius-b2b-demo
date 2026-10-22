<?php

declare(strict_types=1);

namespace App\Insights;

use Tmonier\SyliusCompanyInsightsPlugin\Breakdown\OrderDimensionProviderInterface;

/** The "cost centre" axis of the purchasing dashboard (US-152): the code typed at the checkout (ATELIER-01, MAINT-02...). */
final readonly class CostCenterDimensionProvider implements OrderDimensionProviderInterface
{
    public function __construct(private OrderColumns $columns)
    {
    }

    public function code(): string
    {
        return 'cost-center';
    }

    public function label(): string
    {
        return 'app.ui.cost_center';
    }

    public function values(array $orderIds): array
    {
        return $this->columns->read('cost_center', $orderIds);
    }
}
