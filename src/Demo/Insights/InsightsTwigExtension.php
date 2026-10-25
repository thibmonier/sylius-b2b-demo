<?php

declare(strict_types=1);

namespace App\Demo\Insights;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** US-201: `demo_insights_chart(currentValues, previousValues, unit)` for the monthly chart of the purchasing dashboard. */
final class InsightsTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [new TwigFunction('demo_insights_chart', MonthlyChart::plan(...))];
    }
}
