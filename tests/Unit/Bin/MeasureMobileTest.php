<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * US-071 / T-071-01: structure of bin/measure-mobile. The measure itself needs Chromium and the running demo; here only
 * what makes it a valid regression guard is pinned: real mobile emulation, both widths, the seven pages, and a
 * failure that names the page and the width.
 */
final class MeasureMobileTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function pages(): iterable
    {
        foreach (['connexion' => 'login', 'accueil' => "path: ''", 'categorie' => 'taxons/', 'fiche produit' => 'products/', 'panier' => 'cart/', 'devis' => 'quote-request', 'plugins' => 'plugins'] as $name => $path) {
            yield $name => [$path];
        }
    }

    #[DataProvider('pages')]
    public function testItMeasuresEveryPageOfTheOpeningPath(string $path): void
    {
        self::assertStringContainsString($path, $this->script());
    }

    public function testItEmulatesAMobileAtThe375And320Widths(): void
    {
        $script = $this->script();

        self::assertStringContainsString('const WIDTHS = [375, 320];', $script);
        self::assertStringContainsString('isMobile: true', $script, 'headless Chrome alone never goes below ~500 px');
        self::assertStringContainsString('document.documentElement.scrollWidth', $script);
    }

    public function testItFailsNamingThePageAndTheWidth(): void
    {
        $script = $this->script();

        self::assertStringContainsString("console.log(`\${ok ? 'OK' : 'KO'}  \${String(width).padStart(3)} px  \${name}", $script);
        self::assertStringContainsString('failures > 0 ? 1 : 0', $script);
    }

    public function testItChecksTheTouchTargetsAndTheDemoBar(): void
    {
        $script = $this->script();

        self::assertStringContainsString('const MIN_TARGET = 44;', $script);
        self::assertStringContainsString('[data-demo-banner]', $script);
        self::assertStringContainsString('1280', $script);
    }

    public function testItIsExecutable(): void
    {
        self::assertTrue(is_executable(\dirname(__DIR__, 3) . '/bin/measure-mobile'));
    }

    private function script(): string
    {
        return (string) file_get_contents(\dirname(__DIR__, 3) . '/bin/measure-mobile');
    }
}
