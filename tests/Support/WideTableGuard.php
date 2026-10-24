<?php

declare(strict_types=1);

namespace App\Tests\Support;

/**
 * T-TECH-27-06: finds the Twig pages of a directory with a table of four columns or more that does not fit a phone. A wide table is
 * compliant when it sits in its own scroll region (an enclosing element with `overflow-x: auto`, or `table-responsive`), or when the page
 * turns its rows into cards below 768 px: in a `@media (max-width: …)` block, the cells are `display: block`, the header row is hidden
 * visually (`clip`, never `display: none`, which would hide the headings from the screen readers), the heading of the column is shown
 * before the value (`content: attr(data-label)`), and every cell of the body carries its `data-label`.
 */
final class WideTableGuard
{
    private const WIDE = 4;

    private const BREAKPOINT_MAX = 800;

    /** @return list<string> the templates to fix, relative to $directory, sorted */
    public static function offenders(string $directory): array
    {
        $offenders = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || !str_ends_with($file->getFilename(), '.twig')) {
                continue;
            }
            $twig = (string) preg_replace('/\{#.*?#\}/s', '', (string) file_get_contents($file->getPathname()));
            if (self::hasAnOffendingTable($twig)) {
                $offenders[] = substr($file->getPathname(), \strlen($directory) + 1);
            }
        }
        sort($offenders);

        return $offenders;
    }

    private static function hasAnOffendingTable(string $twig): bool
    {
        $cards = self::cardsCss($twig);
        preg_match_all('#<table\b.*?</table>#s', $twig, $tables, \PREG_OFFSET_CAPTURE);
        foreach ($tables[0] as [$table, $offset]) {
            if (self::columns($table) < self::WIDE || self::scrolls(substr($twig, 0, $offset))) {
                continue;
            }
            if (!$cards || !self::everyCellIsLabelled($table)) {
                return true;
            }
        }

        return false;
    }

    private static function columns(string $table): int
    {
        $head = 1 === preg_match('#<thead\b.*?</thead>#s', $table, $match) ? $match[0] : (preg_match('#<tr\b.*?</tr>#s', $table, $first) ? $first[0] : '');
        $columns = (int) preg_match_all('/<th\b/', $head);
        if (preg_match_all('#\{%\s*for\s+\w+\s+in\s+\[([^\]]*)\]\s*%\}(?:(?!\{%\s*endfor).)*<th#s', $head, $loops)) {
            foreach ($loops[1] as $items) {
                $columns += substr_count($items, ',');
            }
        }

        return $columns;
    }

    /** Whether the last opened element before the table is a scroll region of its own. */
    private static function scrolls(string $before): bool
    {
        $position = strrpos($before, '<div');

        return false !== $position && 1 === preg_match('/overflow(?:-x)?\s*:\s*auto|table-responsive/', substr($before, $position)) && !str_contains(substr($before, $position), '</div>');
    }

    private static function everyCellIsLabelled(string $table): bool
    {
        $body = 1 === preg_match('#<tbody\b.*?</tbody>#s', $table, $match) ? $match[0] : $table;
        preg_match_all('#<td\b[^>]*>#', $body, $cells);
        foreach ($cells[0] as $cell) {
            if (!str_contains($cell, 'data-label=')) {
                return false;
            }
        }

        return true;
    }

    /** The page turns the rows into cards below the breakpoint. */
    private static function cardsCss(string $twig): bool
    {
        preg_match_all('#<style\b[^>]*>(.*?)</style>#s', $twig, $styles);
        $css = implode("\n", $styles[1]);
        $phone = '';
        foreach (self::mediaBlocks($css) as $block) {
            $phone .= $block . "\n";
        }

        return 1 === preg_match('/(?:^|[\s,}])(?:[^{}]*\btd\b[^{}]*)\{[^}]*display:\s*block/', $phone) &&
            1 === preg_match('/thead[^{}]*\{[^}]*clip:/', $phone) &&
            1 === preg_match('/td::before[^{}]*\{[^}]*content:\s*attr\(data-label\)/', $phone) &&
            1 !== preg_match('/thead[^{}]*\{[^}]*display:\s*none/', $phone);
    }

    /** @return list<string> the body of each `@media (max-width: …)` block of a width up to the breakpoint */
    private static function mediaBlocks(string $css): array
    {
        $blocks = [];
        $offset = 0;
        while (1 === preg_match('/@media[^{]*\(max-width:\s*(\d+(?:\.\d+)?)px\)[^{]*\{/', $css, $match, \PREG_OFFSET_CAPTURE, $offset)) {
            $open = $match[0][1] + \strlen($match[0][0]) - 1;
            $depth = 0;
            $end = \strlen($css);
            for ($i = $open; $i < \strlen($css); ++$i) {
                $depth += '{' === $css[$i] ? 1 : ('}' === $css[$i] ? -1 : 0);
                if (0 === $depth) {
                    $end = $i;

                    break;
                }
            }
            if ((float) $match[1][0] <= self::BREAKPOINT_MAX) {
                $blocks[] = substr($css, $open + 1, $end - $open - 1);
            }
            $offset = $end + 1;
        }

        return $blocks;
    }
}
