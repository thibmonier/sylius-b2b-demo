<?php

declare(strict_types=1);

namespace App\Tests\Unit\Theme;

use App\Tests\Support\StyleColorScanner;
use PHPUnit\Framework\TestCase;

/** US-204: the styles of the "Plugins" page (partial _plugins.scss): tokens only, 44 px targets, no animation without the reduced-motion guard. */
final class PluginsStylesTest extends TestCase
{
    private const STYLES = __DIR__ . '/../../../assets/shop/styles';

    public function testThePartialExistsAndIsImportedByTheB2bStylesheet(): void
    {
        self::assertFileExists(self::STYLES . '/_plugins.scss');
        self::assertStringContainsString("@use 'plugins';", $this->read('b2b.scss'));
    }

    public function testThePartialWritesNoColourByHand(): void
    {
        self::assertSame([], StyleColorScanner::hardCoded($this->read('_plugins.scss')));
    }

    public function testLinksAndChipsKeepA44PixelTarget(): void
    {
        $styles = $this->read('_plugins.scss');

        self::assertGreaterThanOrEqual(3, substr_count($styles, 'min-height: 2.75rem'), 'chips, links and the details summary');
    }

    public function testNoTransitionOrAnimationWithoutTheReducedMotionGuard(): void
    {
        $styles = $this->read('_plugins.scss');

        if (1 === preg_match('/\b(?:transition|animation)\s*:/', $styles)) {
            self::assertStringContainsString('prefers-reduced-motion', $styles);
        }
        self::assertSame(0, preg_match('/(?<!min-|max-)width\s*:\s*\d+(?:\.\d+)?px/', $styles), 'no fixed width in pixels: 320 px has no horizontal scroll');
    }

    private function read(string $file): string
    {
        return (string) file_get_contents(self::STYLES . '/' . $file);
    }
}
