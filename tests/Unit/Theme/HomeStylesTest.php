<?php

declare(strict_types=1);

namespace App\Tests\Unit\Theme;

use App\Tests\Support\StyleColorScanner;
use PHPUnit\Framework\TestCase;

/**
 * US-186: what the stylesheet of the home guarantees on a 360 px screen (the measure itself is bin/measure-mobile and the
 * check in production): grids that may shrink, no viewport-wide box, 44 px targets, no animation without a reduced-motion rule.
 */
final class HomeStylesTest extends TestCase
{
    public function testTheHomeStylesheetIsPartOfTheB2bEntry(): void
    {
        self::assertStringContainsString("@use 'home';", $this->read('b2b.scss'));
    }

    public function testGridsMayShrinkBelowTheirContent(): void
    {
        $styles = $this->read('_home.scss');

        self::assertGreaterThanOrEqual(3, substr_count($styles, 'minmax(0, 1fr)'));
        self::assertStringNotContainsString('100vw', $styles, '100vw adds the width of the scrollbar and widens the page');
        self::assertDoesNotMatchRegularExpression('/(?<!max-)(?<!min-)width:\s*(?:3[6-9]\d|[4-9]\d\d|\d{4,})px/', $styles, 'no fixed width wider than a phone');
    }

    public function testEveryControlIsAFortyFourPixelTarget(): void
    {
        self::assertMatchesRegularExpression('/\.b2b-home-btn\s*\{[^}]*min-height:\s*2\.75rem/s', $this->read('_home.scss'));
    }

    public function testTransitionsAreSwitchedOffForReducedMotion(): void
    {
        $styles = $this->read('_home.scss');

        self::assertStringContainsString('transition', $styles);
        self::assertMatchesRegularExpression('/@media \(prefers-reduced-motion: reduce\)\s*\{[^@]*transition:\s*none/s', $styles);
    }

    /** The header menu is a row only when its own column is wide enough (the account block of a signed-in buyer is wide): a container query, no screen width. */
    public function testTheHeaderMenuFoldsByContainerQuery(): void
    {
        $styles = $this->read('_home.scss');

        self::assertMatchesRegularExpression('/\.b2b-nav-col\s*\{[^}]*container-type:\s*inline-size/s', $styles);
        self::assertMatchesRegularExpression('/@container\s*\(min-width:\s*4\d(?:\.\d+)?rem\)\s*\{[^@]*b2b-nav__list--row[^@]*b2b-nav__fold/s', $styles);
    }

    /** US-192: the greeting menu is a wide-screen control: its caret is drawn from lg only, the phone keeps its icon button. */
    public function testTheGreetingCaretShowsFromTheLgWidthOnly(): void
    {
        $styles = $this->read('_home.scss');

        self::assertMatchesRegularExpression('/\.b2b-account-menu[^{]*\{[^}]*::after\s*\{[^}]*display:\s*none/s', $styles);
        self::assertMatchesRegularExpression('/@media \(min-width: 992px\)\s*\{[^@]*\.b2b-account-menu[^@]*::after[^@]*display:\s*inline-block/s', $styles);
    }

    public function testTheHomeWritesNoColourByHand(): void
    {
        self::assertSame([], StyleColorScanner::hardCoded($this->read('_home.scss')));
    }

    private function read(string $file): string
    {
        return (string) file_get_contents(__DIR__ . '/../../../assets/shop/styles/' . $file);
    }
}
