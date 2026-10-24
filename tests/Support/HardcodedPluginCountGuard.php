<?php

declare(strict_types=1);

namespace App\Tests\Support;

/**
 * T-TECH-27-02: finds the assertions of the tests that type the number of plugins of the demo (a number of two digits or more compared
 * to something that speaks of plugins, of the catalogue, of slugs or of groups). The count comes from DemoCatalogs::pluginCount().
 */
final class HardcodedPluginCountGuard
{
    private const ASSERTION = '/assert(?:Count|Same|GreaterThanOrEqual|LessThanOrEqual)\(\s*\d{2,}\s*,/';

    private const PLUGIN_WORDS = '/\bcatalog\b|\bcatalog\(|plugin|\bslugs\b|\$groups\b/i';

    /** @return list<string> "file:line" of each offending assertion, relative to the directory, sorted */
    public static function offenders(string $testsDirectory): array
    {
        $offenders = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($testsDirectory, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || 'php' !== $file->getExtension() || self::isExcluded($file->getPathname())) {
                continue;
            }
            foreach (file($file->getPathname()) ?: [] as $index => $line) {
                if (1 === preg_match(self::ASSERTION, $line) && 1 === preg_match(self::PLUGIN_WORDS, $line)) {
                    $offenders[] = substr($file->getPathname(), \strlen($testsDirectory) + 1) . ':' . ($index + 1);
                }
            }
        }
        sort($offenders);

        return $offenders;
    }

    /** The fixtures of data and the guard tests, which hold literals as samples. */
    private static function isExcluded(string $path): bool
    {
        return str_contains($path, '/Fixtures/') || str_ends_with($path, '/Architecture/HardcodedPluginCountTest.php');
    }
}
