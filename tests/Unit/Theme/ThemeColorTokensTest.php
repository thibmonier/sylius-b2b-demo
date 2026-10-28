<?php

declare(strict_types=1);

namespace App\Tests\Unit\Theme;

use App\Tests\Support\ContrastRatio;
use App\Tests\Support\StyleColorScanner;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * US-073: AAA contrast (7:1 text, 3:1 components) of the palette, one token per colour, no colour written by hand
 * outside the token file.
 */
final class ThemeColorTokensTest extends TestCase
{
    private const STYLES = __DIR__ . '/../../../assets/shop/styles';

    /** @return iterable<string, array{string, string}> text pairs: foreground token, background token */
    public static function textPairs(): iterable
    {
        $pairs = [
            ['navy', 'white'], ['navy', 'surface'], ['navy', 'accent-subtle'],
            ['white', 'navy'], ['white', 'navy-dark'],
            ['accent', 'white'], ['accent', 'surface'], ['accent', 'accent-subtle'],
            ['white', 'accent'], ['white', 'accent-dark'],
            ['muted', 'white'], ['muted', 'surface'], ['muted', 'accent-subtle'], ['muted', 'surface-strong'],
            ['success', 'white'], ['warning', 'white'], ['white', 'danger'], ['danger', 'white'],
            // US-186: the editorial home (paper, sand, teal and night backgrounds).
            ['ink', 'paper'], ['navy', 'paper'], ['navy', 'sand'], ['accent', 'paper'], ['accent-on-sand', 'sand'],
            ['muted', 'paper'], ['muted', 'sand'], ['sand-text', 'sand'], ['white', 'teal'], ['teal', 'paper'],
            ['gold', 'night'], ['gold', 'night-raised'], ['night', 'gold'], ['white', 'night'], ['white', 'night-raised'],
            ['night-text', 'night'], ['night-text', 'night-raised'],
            // US-204: badges of the cards of the Plugins page (white cards).
            ['teal', 'white'],
        ];
        foreach ($pairs as [$foreground, $background]) {
            yield $foreground . ' on ' . $background => [$foreground, $background];
        }
    }

    /** @return iterable<string, array{string, string}> component pairs: boundary or focus ring, background */
    public static function componentPairs(): iterable
    {
        foreach ([['navy', 'white'], ['muted', 'white'], ['accent', 'white'], ['accent', 'surface'], ['accent-bright', 'paper'], ['accent-bright', 'sand'], ['night-line', 'night'], ['gold', 'night']] as [$foreground, $background]) {
            yield $foreground . ' on ' . $background => [$foreground, $background];
        }
    }

    /** US-186: the bright orange of the mock-up is kept for titles of 24 px and more (4.5:1), never for running text or buttons. */
    public function testTheBrightOrangeIsReservedToLargeText(): void
    {
        $tokens = $this->tokens();

        self::assertGreaterThanOrEqual(4.5, ContrastRatio::between($tokens['accent-bright'], $tokens['paper']));
        self::assertLessThan(7.0, ContrastRatio::between($tokens['accent-bright'], $tokens['white']), 'it is not a text colour: the accent token is');
    }

    #[DataProvider('textPairs')]
    public function testTextReachesSevenToOne(string $foreground, string $background): void
    {
        $tokens = $this->tokens();

        self::assertGreaterThanOrEqual(7.0, ContrastRatio::between($tokens[$foreground], $tokens[$background]), $foreground . ' on ' . $background);
    }

    #[DataProvider('componentPairs')]
    public function testComponentsReachThreeToOne(string $foreground, string $background): void
    {
        $tokens = $this->tokens();

        self::assertGreaterThanOrEqual(3.0, ContrastRatio::between($tokens[$foreground], $tokens[$background]), $foreground . ' on ' . $background);
    }

    public function testEachColourIsDeclaredOnce(): void
    {
        $values = array_map('strtolower', $this->tokens());

        self::assertSame($values, array_unique($values), 'two tokens carry the same colour');
    }

    public function testASingleBrownRemains(): void
    {
        $styles = $this->read('_variables.scss') . $this->read('b2b.scss');

        self::assertSame(0, preg_match('/#963d00|#9a3f00/i', $styles), 'the two original browns are gone');
        self::assertArrayHasKey('accent', $this->tokens());
    }

    public function testB2bStylesheetWritesNoColourByHand(): void
    {
        self::assertSame([], StyleColorScanner::hardCoded($this->read('b2b.scss')));
    }

    public function testTheScannerReportsAColourWrittenByHand(): void
    {
        $found = StyleColorScanner::hardCoded("a { color: \$b2b-navy; }\nb { color: #d63384; }\nc { background: rgba(0, 0, 0, .5); } // #abc\nd { border-color: ButtonText; }");

        self::assertCount(2, $found);
        self::assertStringContainsString('line 2', $found[0]);
        self::assertStringContainsString('#d63384', $found[0]);
        self::assertStringContainsString('line 3', $found[1]);
    }

    public function testComponentsOfTheCoreThemeAreRecoloured(): void
    {
        $styles = $this->read('b2b.scss');

        foreach (['.pagination', '.btn-outline-secondary', '--bs-code-color', '--bs-secondary-color', '.text-black-50'] as $selector) {
            self::assertStringContainsString($selector, $styles);
        }
    }

    public function testHighContrastPreferencesAreHandled(): void
    {
        $styles = $this->read('b2b.scss');

        self::assertStringContainsString('@media (prefers-contrast: more)', $styles);
        self::assertStringContainsString('@media (forced-colors: active)', $styles);
    }

    /** @return array<string, string> token name (without "b2b-") => #rrggbb */
    private function tokens(): array
    {
        preg_match_all('/^\$b2b-([a-z-]+):\s*(#[0-9a-fA-F]{6})\s*;/m', $this->read('_variables.scss'), $matches, \PREG_SET_ORDER);
        $tokens = [];
        foreach ($matches as $match) {
            $tokens[$match[1]] = $match[2];
        }

        return $tokens;
    }

    private function read(string $file): string
    {
        return (string) file_get_contents(self::STYLES . '/' . $file);
    }
}
