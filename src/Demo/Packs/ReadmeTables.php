<?php

declare(strict_types=1);

namespace App\Demo\Packs;

/**
 * What a pack README says (US-178), read as tables and never copied by hand: the plugins and their role, the suggested
 * plugins, the steps left to do by hand. A table is recognised by its shape, not by its heading, so the English and the
 * French README read the same way. Markdown links are reduced to their text; backticks are kept for the page to show as code.
 */
final class ReadmeTables
{
    private const string PLUGIN_CELL = '/^\[(sylius-[\w-]+-plugin)\]\(/';

    /**
     * @return array{
     *     title: ?string,
     *     summary: ?string,
     *     command: ?string,
     *     plugins: list<array{slug: string, bound: string, role: string}>,
     *     suggests: list<array{slug: string, reason: string}>,
     *     steps: list<array{slug: string, bundles: list<string>, config: list<string>, routes: list<string>, migrations: bool, other: string}>
     * }
     */
    public static function parse(string $readme): array
    {
        $plugins = [];
        $suggests = [];
        $steps = [];
        foreach (explode("\n", $readme) as $line) {
            $cells = self::cells($line);
            if (3 === \count($cells) && 1 === preg_match(self::PLUGIN_CELL, $cells[0], $match) && 1 === preg_match('/^`([^`]+)`$/', $cells[1], $bound)) {
                $plugins[] = ['slug' => $match[1], 'bound' => $bound[1], 'role' => self::text($cells[2])];
            } elseif (2 === \count($cells) && 1 === preg_match(self::PLUGIN_CELL, $cells[0], $match)) {
                $suggests[] = ['slug' => $match[1], 'reason' => self::text($cells[1])];
            } elseif (6 === \count($cells) && 1 === preg_match('/^[a-z][\w-]*$/', $cells[0]) && str_contains($cells[1], '`')) {
                $steps[] = [
                    'slug' => 'sylius-' . $cells[0] . '-plugin',
                    'bundles' => self::codes($cells[1]),
                    'config' => self::codes($cells[2]),
                    'routes' => self::codes($cells[3]),
                    'migrations' => 1 === preg_match('/^(?:yes|oui)\b/i', $cells[4]),
                    'other' => self::text($cells[5]),
                ];
            }
        }

        return [
            'title' => 1 === preg_match('/^#\s+(?:Sylius Pack\s*:\s*)?(.+?)\s*$/m', $readme, $title) ? $title[1] : null,
            'summary' => 1 === preg_match('/^\*\*(.+)\*\*\s*$/m', $readme, $summary) ? $summary[1] : null,
            'command' => 1 === preg_match('/^(composer require \S+)\s*$/m', $readme, $command) ? $command[1] : null,
            'plugins' => $plugins,
            'suggests' => $suggests,
            'steps' => $steps,
        ];
    }

    /** @return list<string> */
    private static function cells(string $line): array
    {
        $line = trim($line);
        if (!str_starts_with($line, '|')) {
            return [];
        }

        return array_map(trim(...), explode('|', trim($line, '|')));
    }

    /** @return list<string> the code spans of a cell */
    private static function codes(string $cell): array
    {
        preg_match_all('/`([^`]+)`/', $cell, $found);

        return $found[1];
    }

    private static function text(string $cell): string
    {
        return trim((string) preg_replace('/\[([^\]]+)\]\([^)]*\)/', '$1', $cell));
    }
}
