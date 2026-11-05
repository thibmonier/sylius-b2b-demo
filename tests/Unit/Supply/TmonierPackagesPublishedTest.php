<?php

declare(strict_types=1);

namespace App\Tests\Unit\Supply;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * US-228: the demo consumes the plugins and the theme as published on Packagist. A `dev-*` constraint (a branch, not a
 * release) on a tmonier/* package would make the build depend on a moving branch, so it fails here.
 */
final class TmonierPackagesPublishedTest extends TestCase
{
    public function testNoTmonierPackageIsRequiredOnADevBranch(): void
    {
        $composer = json_decode((string) file_get_contents(__DIR__ . '/../../../composer.json'), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($composer);

        self::assertSame([], self::devReferences($composer), 'tmonier/* packages must be required on a published version, never dev-*.');
    }

    public function testTheLockedVersionsAreReleases(): void
    {
        $lock = json_decode((string) file_get_contents(__DIR__ . '/../../../composer.lock'), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($lock);
        $locked = array_merge((array) ($lock['packages'] ?? []), (array) ($lock['packages-dev'] ?? []));

        $branches = [];
        foreach ($locked as $package) {
            if (!\is_array($package) || !\is_string($package['name'] ?? null) || !\is_string($package['version'] ?? null)) {
                continue;
            }
            if (str_starts_with($package['name'], 'tmonier/') && str_starts_with($package['version'], 'dev-')) {
                $branches[] = $package['name'] . ' ' . $package['version'];
            }
        }

        self::assertSame([], $branches);
    }

    public function testTheThemeIsInstalledFromAPublishedVersion(): void
    {
        $composer = json_decode((string) file_get_contents(__DIR__ . '/../../../composer.json'), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($composer);
        $require = $composer['require'] ?? [];
        self::assertIsArray($require);

        self::assertArrayHasKey('tmonier/sylius-industry-trade-theme', $require);
    }

    /**
     * @param array<string, mixed> $composer
     * @param list<string>         $expected
     */
    #[DataProvider('composerFiles')]
    public function testADevReferenceIsDetected(array $composer, array $expected): void
    {
        self::assertSame($expected, self::devReferences($composer));
    }

    /** @return iterable<string, array{array<string, mixed>, list<string>}> */
    public static function composerFiles(): iterable
    {
        yield 'dev-main' => [['require' => ['tmonier/a' => 'dev-main']], ['tmonier/a dev-main']];
        yield 'branch alias' => [['require-dev' => ['tmonier/b' => '1.x-dev']], ['tmonier/b 1.x-dev']];
        yield 'stability flag' => [['require' => ['tmonier/c' => '^1.0@dev']], ['tmonier/c ^1.0@dev']];
        yield 'published' => [['require' => ['tmonier/d' => '^1.2', 'other/e' => 'dev-main']], []];
    }

    /**
     * @param array<string, mixed> $composer
     *
     * @return list<string>
     */
    private static function devReferences(array $composer): array
    {
        $found = [];
        foreach (['require', 'require-dev'] as $section) {
            $packages = $composer[$section] ?? [];
            foreach (\is_array($packages) ? $packages : [] as $name => $constraint) {
                if (\is_string($constraint) && str_starts_with((string) $name, 'tmonier/') && 1 === preg_match('/(^dev-|-dev$|@dev)/', $constraint)) {
                    $found[] = $name . ' ' . $constraint;
                }
            }
        }

        return $found;
    }
}
