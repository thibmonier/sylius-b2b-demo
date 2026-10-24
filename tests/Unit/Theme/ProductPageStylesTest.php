<?php

declare(strict_types=1);

namespace App\Tests\Unit\Theme;

use App\Tests\Support\StyleColorScanner;
use PHPUnit\Framework\TestCase;

/** US-189: what the stylesheet of the product page guarantees on a phone and for a long name, with the tokens of the home. */
final class ProductPageStylesTest extends TestCase
{
    public function testTheStylesheetIsPartOfTheB2bEntryAndWritesNoColourByHand(): void
    {
        self::assertStringContainsString("@use 'pdp';", $this->read('b2b.scss'));
        self::assertSame([], StyleColorScanner::hardCoded($this->read('_pdp.scss')));
    }

    public function testGridsMayShrinkAndNothingIsWiderThanAPhone(): void
    {
        $styles = $this->read('_pdp.scss');

        self::assertGreaterThanOrEqual(3, substr_count($styles, 'minmax(0, 1fr)'));
        self::assertStringNotContainsString('100vw', $styles);
        self::assertDoesNotMatchRegularExpression('/(?<!max-)(?<!min-)width:\s*(?:3[6-9]\d|[4-9]\d\d|\d{4,})px/', $styles);
    }

    public function testALongNameWrapsInsteadOfWideningTheColumn(): void
    {
        self::assertMatchesRegularExpression('/\.b2b-pdp h1[^{]*\{[^}]*(?:overflow-wrap:\s*anywhere|text-wrap:\s*balance)/s', $this->read('_pdp.scss'));
        self::assertMatchesRegularExpression('/\.b2b-pdp h1[^{]*\{[^}]*font-family:\s*var\(--b2b-font-serif\)/s', $this->read('_pdp.scss'));
    }

    /** US-189 (tightening): from lg the gallery follows the reading of the buy box (sticky), the phone keeps the normal flow. */
    public function testTheGalleryIsStickyFromTheLgWidthOnly(): void
    {
        $styles = $this->read('_pdp.scss');

        self::assertMatchesRegularExpression('/&__gallery\s*\{.*?@media \(min-width: 992px\)\s*\{[^}]*position:\s*sticky[^}]*top:\s*[\d.]+rem/s', $styles);
    }

    public function testEveryControlIsAFortyFourPixelTargetAndMotionIsReducedOnRequest(): void
    {
        $styles = $this->read('_pdp.scss');

        self::assertMatchesRegularExpression('/min-height:\s*2\.75rem/', $styles);
        self::assertMatchesRegularExpression('/@media \(prefers-reduced-motion: reduce\)\s*\{[^@]*transition:\s*none/s', $styles);
    }

    private function read(string $file): string
    {
        return (string) file_get_contents(__DIR__ . '/../../../assets/shop/styles/' . $file);
    }
}
