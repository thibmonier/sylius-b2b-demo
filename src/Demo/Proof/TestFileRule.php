<?php

declare(strict_types=1);

namespace App\Demo\Proof;

/**
 * What counts as a test file in the technical proof (US-111, US-168): a `*Test.php` or `*.feature` file under `tests/`.
 * One rule for the GitHub tree read by the refresh and the local tree read by the check, so both count the same thing.
 */
final class TestFileRule
{
    private const string PATTERN = '#^tests/.*(?:Test\.php|\.feature)$#';

    /** @param string $path relative to the repository root, with forward slashes */
    public static function matches(string $path): bool
    {
        return 1 === preg_match(self::PATTERN, $path);
    }

    /** The test files of a local checkout; 0 when it has no tests/ directory. */
    public static function countIn(string $root): int
    {
        if (!is_dir($root . '/tests')) {
            return 0;
        }
        $count = 0;
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root . '/tests', \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && self::matches(substr(strtr($file->getPathname(), '\\', '/'), \strlen($root) + 1))) {
                ++$count;
            }
        }

        return $count;
    }
}
