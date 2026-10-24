<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Home;

use App\Demo\Http\SecurityHeadersSubscriber;
use PHPUnit\Framework\TestCase;

/** US-186: Fraunces and Inter are served by the application itself (CSP `font-src 'self'`), under their OFL licences. */
final class HomeFontsTest extends TestCase
{
    public function testNoStylesheetNorTemplateCallsAFontService(): void
    {
        $root = \dirname(__DIR__, 4);
        foreach (['assets/shop/styles', 'templates'] as $directory) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root . '/' . $directory, \FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                if (!$file instanceof \SplFileInfo || !preg_match('/\.(?:scss|css|twig)$/', $file->getFilename())) {
                    continue;
                }
                $content = (string) file_get_contents($file->getPathname());
                self::assertDoesNotMatchRegularExpression('#fonts\.(?:googleapis|gstatic)\.com|use\.typekit|fonts\.bunny#i', $content, $file->getPathname());
            }
        }
    }

    public function testTheContentSecurityPolicyKeepsFontsOnTheOrigin(): void
    {
        self::assertMatchesRegularExpression("/font-src 'self' data:;/", SecurityHeadersSubscriber::CSP);
    }

    public function testFrauncesIsDeclaredLocallyWithAFallbackAndASwapDisplay(): void
    {
        $fonts = (string) file_get_contents(\dirname(__DIR__, 4) . '/assets/shop/styles/fonts.scss');

        self::assertStringContainsString("font-family: 'FrauncesLatin'", $fonts);
        self::assertMatchesRegularExpression("#url\('\.\./fonts/Fraunces[^']*\.woff2'\) format\('woff2'\)#", $fonts);
        self::assertStringContainsString("font-family: 'FrauncesFallback'", $fonts);
        self::assertStringContainsString('--b2b-font-serif', $fonts);
        self::assertGreaterThanOrEqual(2, substr_count($fonts, 'font-display: swap'), 'Inter and Fraunces both swap');
    }

    public function testEveryFontFileComesWithItsLicence(): void
    {
        $directory = \dirname(__DIR__, 4) . '/assets/shop/fonts';

        foreach (['Fraunces', 'Inter'] as $family) {
            self::assertNotSame([], glob($directory . '/' . $family . '*.woff2') ?: [], $family . ' font file');
            $licence = $directory . '/OFL-' . $family . '.txt';
            self::assertFileExists($licence);
            self::assertStringContainsString('SIL OPEN FONT LICENSE Version 1.1', (string) file_get_contents($licence));
        }
    }

    public function testTheFrauncesFileIsLight(): void
    {
        foreach (glob(\dirname(__DIR__, 4) . '/assets/shop/fonts/Fraunces*.woff2') ?: [] as $file) {
            self::assertLessThan(120_000, filesize($file), basename($file));
        }
    }
}
