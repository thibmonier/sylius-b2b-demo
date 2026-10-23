<?php

declare(strict_types=1);

namespace App\Demo\Proof\Compatibility;

/**
 * The dependency graph of the plugins (US-171), laid out on the server: a plugin sits to the left of what it needs, one
 * column per depth. Only plugins that have a link to another plugin of the catalogue are drawn; the mandatory links and the
 * suggestions are two kinds of edge (the page draws them as a solid and a dashed line, and lists them in text).
 */
final class DependencyGraph
{
    private const int NODE_WIDTH = 220;

    private const int NODE_HEIGHT = 36;

    private const int COLUMN_GAP = 110;

    private const int ROW_GAP = 16;

    /**
     * @param array<string, string> $names the plugins of the catalogue: slug => display name
     *
     * @return array{nodes: list<array{slug: string, name: string, x: int, y: int, width: int, height: int}>, edges: list<array{from: string, to: string, kind: string, x1: int, y1: int, x2: int, y2: int}>, width: int, height: int}
     */
    public static function build(CompatibilityReport $report, array $names): array
    {
        $links = self::links($report, $names);
        if ([] === $links) {
            return ['nodes' => [], 'edges' => [], 'width' => 0, 'height' => 0];
        }
        $depth = self::depths($links);
        $columns = [];
        foreach ($depth as $slug => $level) {
            $columns[$level][] = $slug;
        }
        ksort($columns);
        $columns = array_values($columns);
        $nodes = [];
        $position = [];
        foreach ($columns as $level => $slugs) {
            usort($slugs, static fn (string $a, string $b): int => strcmp($names[$a], $names[$b]));
            foreach ($slugs as $row => $slug) {
                $x = $level * (self::NODE_WIDTH + self::COLUMN_GAP);
                $y = $row * (self::NODE_HEIGHT + self::ROW_GAP);
                $position[$slug] = [$x, $y];
                $nodes[] = ['slug' => $slug, 'name' => $names[$slug], 'x' => $x, 'y' => $y, 'width' => self::NODE_WIDTH, 'height' => self::NODE_HEIGHT];
            }
        }
        $edges = array_map(static fn (array $link): array => $link + [
            'x1' => $position[$link['from']][0] + self::NODE_WIDTH,
            'y1' => $position[$link['from']][1] + intdiv(self::NODE_HEIGHT, 2),
            'x2' => $position[$link['to']][0],
            'y2' => $position[$link['to']][1] + intdiv(self::NODE_HEIGHT, 2),
        ], $links);
        $rows = max(1, ...array_map('count', $columns));

        return [
            'nodes' => $nodes,
            'edges' => $edges,
            'width' => \count($columns) * self::NODE_WIDTH + (\count($columns) - 1) * self::COLUMN_GAP,
            'height' => $rows * self::NODE_HEIGHT + ($rows - 1) * self::ROW_GAP,
        ];
    }

    /**
     * @param array<string, string> $names
     *
     * @return list<array{from: string, to: string, kind: string}>
     */
    private static function links(CompatibilityReport $report, array $names): array
    {
        $links = [];
        foreach (array_keys($names) as $slug) {
            $plugin = $report->plugin($slug);
            foreach (['required' => $plugin->requires, 'optional' => $plugin->suggests] as $kind => $targets) {
                foreach ($targets as $target) {
                    if (isset($names[$target]) && $target !== $slug) {
                        $links[] = ['from' => $slug, 'to' => $target, 'kind' => $kind];
                    }
                }
            }
        }

        return $links;
    }

    /**
     * @param list<array{from: string, to: string, kind: string}> $links
     *
     * @return array<string, int> depth of each drawn plugin (0 = nothing drawn needs it to the left)
     */
    private static function depths(array $links): array
    {
        $depth = [];
        foreach ($links as $link) {
            $depth[$link['from']] = 0;
            $depth[$link['to']] = 0;
        }
        // Relaxation bounded by the number of plugins: a cycle cannot loop forever.
        for ($pass = 0; $pass < \count($depth); ++$pass) {
            foreach ($links as $link) {
                $depth[$link['to']] = max($depth[$link['to']], $depth[$link['from']] + 1);
            }
        }

        return array_map(static fn (int $level): int => min($level, \count($depth) - 1), $depth);
    }
}
