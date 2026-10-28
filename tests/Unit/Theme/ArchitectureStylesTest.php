<?php

declare(strict_types=1);

namespace App\Tests\Unit\Theme;

use App\Tests\Support\StyleColorScanner;
use PHPUnit\Framework\TestCase;

final class ArchitectureStylesTest extends TestCase
{
    private const STYLES = __DIR__ . '/../../../assets/shop/styles';

    public function testThePartialExistsAndIsImportedByTheB2bStylesheet(): void
    {
        self::assertFileExists(self::STYLES . '/_architecture.scss');
        self::assertStringContainsString("@use 'architecture';", $this->read('b2b.scss'));
    }

    public function testThePartialWritesNoColourByHand(): void
    {
        self::assertSame([], StyleColorScanner::hardCoded($this->read('_architecture.scss')));
    }

    public function testAnchorsAndLinksKeepA44PixelTarget(): void
    {
        self::assertGreaterThanOrEqual(3, substr_count($this->read('_architecture.scss'), 'min-height: 2.75rem'), 'anchors, links and numbered badges');
    }

    public function testNoTransitionOrAnimationWithoutTheReducedMotionGuard(): void
    {
        $styles = $this->read('_architecture.scss');

        if (1 === preg_match('/\b(?:transition|animation|scroll-behavior)\s*:/', $styles)) {
            self::assertStringContainsString('prefers-reduced-motion', $styles);
        }
    }

    public function testNoFixedWidthInPixelsSoThePageNeverScrollsSideways(): void
    {
        self::assertSame(0, preg_match('/(?<!min-|max-)width\s*:\s*\d+(?:\.\d+)?px/', $this->read('_architecture.scss')), '320 px has no horizontal scroll');
    }

    public function testTheNumberingIsDrawnByCounters(): void
    {
        self::assertStringContainsString('counter-increment', $this->read('_architecture.scss'));
    }

    private function read(string $file): string
    {
        return (string) file_get_contents(self::STYLES . '/' . $file);
    }
}
