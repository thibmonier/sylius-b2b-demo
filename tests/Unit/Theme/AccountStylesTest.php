<?php

declare(strict_types=1);

namespace App\Tests\Unit\Theme;

use App\Tests\Support\StyleColorScanner;
use PHPUnit\Framework\TestCase;

/**
 * US-199: what the stylesheet of "My account" guarantees on a 360 px screen (the measure itself is bin/measure-mobile):
 * grids that may shrink, no viewport-wide box, 44 px targets, no animation without a reduced-motion rule, the colours of the tokens.
 */
final class AccountStylesTest extends TestCase
{
    public function testTheAccountStylesheetIsPartOfTheB2bEntry(): void
    {
        self::assertStringContainsString("@use 'account';", $this->read('b2b.scss'));
    }

    public function testGridsMayShrinkBelowTheirContent(): void
    {
        $styles = $this->read('_account.scss');

        self::assertGreaterThanOrEqual(4, substr_count($styles, 'minmax(0, 1fr)'));
        self::assertStringNotContainsString('100vw', $styles);
        self::assertDoesNotMatchRegularExpression('/(?<!max-)(?<!min-)width:\s*(?:3[6-9]\d|[4-9]\d\d|\d{4,})px/', $styles, 'no fixed width wider than a phone');
    }

    public function testTheTableScrollsInsideItsRegionNotThePage(): void
    {
        self::assertMatchesRegularExpression('/\.b2b-account__scroll\s*\{[^}]*overflow-x:\s*auto/s', $this->read('_account.scss'));
    }

    public function testEveryLinkOfTheMenuAndEveryActionIsAFortyFourPixelTarget(): void
    {
        $styles = $this->read('_account.scss');

        self::assertMatchesRegularExpression('/\.b2b-account-nav__link\s*\{[^}]*min-height:\s*2\.75rem/s', $styles);
        self::assertMatchesRegularExpression('/\.b2b-account__action\s*\{[^}]*min-height:\s*2\.75rem/s', $styles);
    }

    public function testTheMenuComesAfterThePageOnAPhone(): void
    {
        self::assertMatchesRegularExpression('/@media \(max-width: 991\.98px\)\s*\{[^@]*b2b-account-nav-col[^@]*order:\s*2/s', $this->read('_account.scss'));
    }

    public function testTransitionsAreSwitchedOffForReducedMotion(): void
    {
        $styles = $this->read('_account.scss');

        if (str_contains($styles, 'transition')) {
            self::assertMatchesRegularExpression('/@media \(prefers-reduced-motion: reduce\)\s*\{[^@]*transition:\s*none/s', $styles);
        }
        self::assertStringNotContainsString('animation', $styles);
    }

    public function testColoursComeFromTheTokens(): void
    {
        self::assertSame([], StyleColorScanner::hardCoded($this->read('_account.scss')));
    }

    private function read(string $file): string
    {
        $content = file_get_contents(\dirname(__DIR__, 3) . '/assets/shop/styles/' . $file);
        self::assertIsString($content, $file . ' must exist');

        return $content;
    }
}
