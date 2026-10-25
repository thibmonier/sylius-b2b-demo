<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Insights;

use App\Demo\Insights\MonthlyChart;
use PHPUnit\Framework\TestCase;

/** US-201: the geometry of the monthly chart drawn by the server (no script, nothing to download). */
final class MonthlyChartTest extends TestCase
{
    public function testTheScaleStopsAtAReadableStepAboveTheLargestValue(): void
    {
        $plan = MonthlyChart::plan([1, 6, 2], [0, 3, 0], 1);

        self::assertSame(6, $plan['top']);
        self::assertSame([0, 2, 4, 6], array_column($plan['ticks'], 'value'));
    }

    public function testAmountsAreScaledInWholeCurrencyUnitsNotInCents(): void
    {
        $plan = MonthlyChart::plan([821062, 0], [589280, 0], 100);

        self::assertSame(1_000_000, $plan['top']);
        self::assertSame([0, 200_000, 400_000, 600_000, 800_000, 1_000_000], array_column($plan['ticks'], 'value'));
    }

    public function testAHeightIsProportionalToItsValue(): void
    {
        $plan = MonthlyChart::plan([6, 3, 0], [0, 6, 0], 1);

        self::assertEqualsWithDelta(MonthlyChart::PLOT_HEIGHT, $plan['bars'][0]['height'], 0.01);
        self::assertEqualsWithDelta(MonthlyChart::PLOT_HEIGHT / 2, $plan['bars'][1]['height'], 0.01);
        self::assertSame(0.0, $plan['bars'][2]['height']);
        self::assertSame(0.0, $plan['bars'][0]['previousHeight'], 'No purchase the year before: no bar, not a thin one.');
        self::assertEqualsWithDelta(MonthlyChart::PLOT_HEIGHT, $plan['bars'][1]['previousHeight'], 0.01);
    }

    public function testABarSitsOnTheAxisAtTheBottomOfThePlot(): void
    {
        $plan = MonthlyChart::plan([6], [3], 1);

        self::assertEqualsWithDelta(MonthlyChart::PLOT_TOP, $plan['bars'][0]['y'], 0.01);
        self::assertEqualsWithDelta(MonthlyChart::PLOT_TOP + MonthlyChart::PLOT_HEIGHT / 2, $plan['bars'][0]['previousY'], 0.01);
        self::assertEqualsWithDelta(MonthlyChart::PLOT_TOP + MonthlyChart::PLOT_HEIGHT, $plan['ticks'][0]['y'], 0.01, 'Zero is the axis.');
    }

    public function testNothingToDrawStillGivesAScaleWithoutDividingByZero(): void
    {
        $plan = MonthlyChart::plan([0, 0], [0, 0], 100);

        self::assertGreaterThan(0, $plan['top']);
        self::assertSame(0.0, $plan['bars'][0]['height']);
    }

    public function testTheLastOfTwelveMonthsStaysInsideTheDrawing(): void
    {
        $plan = MonthlyChart::plan(array_fill(0, 12, 1), array_fill(0, 12, 1), 1);

        self::assertCount(12, $plan['bars']);
        $last = $plan['bars'][11];
        self::assertLessThanOrEqual(MonthlyChart::WIDTH, $last['x'] + MonthlyChart::BAR);
        self::assertLessThan($plan['bars'][0]['x'], $plan['bars'][0]['previousX'], 'The year before sits on the left of the month.');
        self::assertGreaterThanOrEqual($plan['bars'][0]['previousX'] + MonthlyChart::BAR, $plan['bars'][0]['x'] - 1, 'The two bars of a month do not overlap.');
    }
}
