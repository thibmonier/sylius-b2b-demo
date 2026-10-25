<?php

declare(strict_types=1);

namespace App\Demo\Insights;

/**
 * US-201: the geometry of the monthly chart of the purchasing dashboard (twelve months, each next to the same month of the year
 * before). The server draws it as SVG: no script, no request, nothing to download; the table under the chart carries the figures.
 */
final class MonthlyChart
{
    public const WIDTH = 960;

    public const PLOT_TOP = 20;

    public const PLOT_HEIGHT = 230;

    public const LEFT = 80;

    public const SLOT = 72;

    public const BAR = 26;

    /** Horizontal gap between the bar of the year before and the bar of the month. */
    private const GAP = 2;

    /** The scale has at most this many intervals. */
    private const MAX_INTERVALS = 5;

    /**
     * @param list<int> $current  one value per month, oldest first
     * @param list<int> $previous the same months of the year before
     * @param int       $unit     smallest step that reads well (100 for amounts in cents, 1 for a count)
     *
     * @return array{top: int, ticks: list<array{value: int, y: float}>, bars: list<array{x: int, y: float, height: float, previousX: int, previousY: float, previousHeight: float}>}
     */
    public static function plan(array $current, array $previous, int $unit): array
    {
        $step = self::step(max([0, ...$current, ...$previous]), $unit);
        $top = max(1, (int) ceil(max([0, ...$current, ...$previous]) / $step)) * $step;

        $ticks = [];
        for ($value = 0; $value <= $top; $value += $step) {
            $ticks[] = ['value' => $value, 'y' => self::y($value, $top)];
        }

        $bars = [];
        foreach ($current as $index => $value) {
            $left = self::LEFT + $index * self::SLOT;
            $before = $previous[$index] ?? 0;
            $bars[] = [
                'x' => $left + self::BAR + self::GAP,
                'y' => self::y($value, $top),
                'height' => self::height($value, $top),
                'previousX' => $left,
                'previousY' => self::y($before, $top),
                'previousHeight' => self::height($before, $top),
            ];
        }

        return ['top' => $top, 'ticks' => $ticks, 'bars' => $bars];
    }

    /** The first of 1, 2, 5, 10, 20, 50... (times the unit) that cuts the largest value in at most five intervals. */
    private static function step(int $largest, int $unit): int
    {
        $units = max(1, (int) ceil($largest / $unit));
        for ($magnitude = 1; ; $magnitude *= 10) {
            foreach ([1, 2, 5] as $factor) {
                if (ceil($units / ($factor * $magnitude)) <= self::MAX_INTERVALS) {
                    return $factor * $magnitude * $unit;
                }
            }
        }
    }

    private static function height(int $value, int $top): float
    {
        return $value <= 0 ? 0.0 : self::PLOT_HEIGHT * $value / $top;
    }

    private static function y(int $value, int $top): float
    {
        return self::PLOT_TOP + self::PLOT_HEIGHT - self::height($value, $top);
    }
}
