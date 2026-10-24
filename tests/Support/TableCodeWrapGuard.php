<?php

declare(strict_types=1);

namespace App\Tests\Support;

/**
 * US-182: finds the Twig pages of a directory with `code` in a table that do not declare `overflow-wrap: anywhere` for
 * these `code`, outside any media query. A page is concerned when a `<table>` holds a `<code>`, written in the table, in
 * a macro of the page that the table calls, or in a partial that it includes. The rule is the page's own (its <style>) or,
 * for a page that extends a layout, the common stylesheet given by the caller. A rule on the cell does not count:
 * Bootstrap's `word-wrap: break-word` on `code` beats what the cell passes down.
 */
final class TableCodeWrapGuard
{
    /** @return list<string> the templates to fix, relative to $directory, sorted */
    public static function offenders(string $directory, string $commonCss): array
    {
        $offenders = [];
        foreach (self::templates($directory) as $path => $relative) {
            $twig = self::withoutComments((string) file_get_contents($path));
            if (!self::hasCodeInATable($twig, $directory)) {
                continue;
            }
            $css = self::withoutMediaQueries(self::styles($twig) . (str_contains($twig, '{% extends') ? "\n" . $commonCss : ''));
            if (!self::wraps($css)) {
                $offenders[] = $relative;
            }
        }
        sort($offenders);

        return $offenders;
    }

    /** @return array<string, string> absolute path => path relative to the directory */
    private static function templates(string $directory): array
    {
        $templates = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && str_ends_with($file->getFilename(), '.twig')) {
                $templates[$file->getPathname()] = substr($file->getPathname(), \strlen($directory) + 1);
            }
        }

        return $templates;
    }

    private static function hasCodeInATable(string $twig, string $directory): bool
    {
        $macros = self::macrosWithCode($twig);
        preg_match_all('#<table\b.*?</table>#s', $twig, $tables);
        foreach ($tables[0] as $table) {
            if (str_contains($table, '<code') || self::callsOne($table, $macros) || self::includesCode($table, $directory)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> names of the macros of the page whose body holds a <code> */
    private static function macrosWithCode(string $twig): array
    {
        preg_match_all('#\{%\s*macro\s+(\w+)\s*\(.*?\{%\s*endmacro\s*%\}#s', $twig, $macros, \PREG_SET_ORDER);

        return array_values(array_map(
            static fn (array $macro): string => $macro[1],
            array_filter($macros, static fn (array $macro): bool => str_contains($macro[0], '<code')),
        ));
    }

    /** @param list<string> $macros */
    private static function callsOne(string $table, array $macros): bool
    {
        foreach ($macros as $name) {
            if (1 === preg_match('#(?<![\w])(?:\w+\.)?' . preg_quote($name, '#') . '\s*\(#', $table)) {
                return true;
            }
        }

        return false;
    }

    private static function includesCode(string $table, string $directory): bool
    {
        preg_match_all("#\\{%\\s*(?:include|embed)\\s+'([^']+)'#", $table, $includes);
        foreach ($includes[1] as $name) {
            foreach ([$directory . '/' . $name, \dirname($directory) . '/' . $name] as $candidate) {
                if (is_file($candidate) && str_contains(self::withoutComments((string) file_get_contents($candidate)), '<code')) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function withoutComments(string $twig): string
    {
        return (string) preg_replace('/\{#.*?#\}/s', '', $twig);
    }

    private static function styles(string $twig): string
    {
        preg_match_all('#<style\b[^>]*>(.*?)</style>#s', $twig, $styles);

        return implode("\n", $styles[1]);
    }

    private static function withoutMediaQueries(string $css): string
    {
        while (false !== ($start = strpos($css, '@media'))) {
            $open = strpos($css, '{', $start);
            if (false === $open) {
                return substr($css, 0, $start);
            }
            $depth = 0;
            $end = \strlen($css);
            for ($i = $open; $i < \strlen($css); ++$i) {
                $depth += '{' === $css[$i] ? 1 : ('}' === $css[$i] ? -1 : 0);
                if (0 === $depth) {
                    $end = $i + 1;

                    break;
                }
            }
            $css = substr($css, 0, $start) . substr($css, $end);
        }

        return $css;
    }

    /** A rule whose selector ends on `code` and that declares `overflow-wrap: anywhere`. */
    private static function wraps(string $css): bool
    {
        preg_match_all('/([^{}]+)\{([^{}]*)\}/', $css, $rules, \PREG_SET_ORDER);
        foreach ($rules as $rule) {
            if (1 !== preg_match('/overflow-wrap\s*:\s*anywhere/', $rule[2])) {
                continue;
            }
            foreach (explode(',', $rule[1]) as $selector) {
                if (1 === preg_match('/(?:^|[\s>+~])code(?:[.\[:#][^\s>+~]*)*\s*$/', $selector)) {
                    return true;
                }
            }
        }

        return false;
    }
}
