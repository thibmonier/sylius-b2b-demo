<?php

declare(strict_types=1);

namespace App\Tests\Unit\Docker;

use App\Tests\Support\Typed;
use PHPUnit\Framework\TestCase;

/**
 * US-147: the demo runs PHP 8.5 everywhere (image, CI, Composer platform), at least the security release 8.5.12.
 * The image itself is pinned by digest (bin/check-pinned-refs, US-081).
 */
final class PhpVersionTest extends TestCase
{
    private const MINOR = '8.5';

    private const FIXED_RELEASE = '8.5.12';

    public function testTheImageIsAFrankenPhpImageOfPhp85PinnedByDigest(): void
    {
        self::assertMatchesRegularExpression(
            '/^ARG PHP_IMAGE=dunglas\/frankenphp:1-php8\.5-bookworm@sha256:[0-9a-f]{64}$/m',
            $this->read('docker/Dockerfile'),
        );
    }

    public function testEveryCiJobSetsUpPhp85(): void
    {
        preg_match_all('/php-version:\s*"([^"]+)"/', $this->read('.github/workflows/ci.yaml'), $matches);

        self::assertNotEmpty($matches[1]);
        self::assertSame(array_fill(0, \count($matches[1]), self::MINOR), $matches[1]);
    }

    public function testComposerRequiresPhp85AndResolvesForTheFixedRelease(): void
    {
        $composer = json_decode($this->read('composer.json'), true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($composer);

        self::assertSame('^' . self::MINOR, Typed::at($composer, 'require', 'php'));
        $platform = Typed::string(Typed::at($composer, 'config', 'platform', 'php'), 'config.platform.php');
        self::assertStringStartsWith(self::MINOR . '.', $platform);
        self::assertTrue(version_compare($platform, self::FIXED_RELEASE, '>='), $platform . ' is older than the security release.');
    }

    private function read(string $path): string
    {
        return (string) file_get_contents(\dirname(__DIR__, 3) . '/' . $path);
    }
}
