<?php

declare(strict_types=1);

namespace App\Tests\Unit\Theme;

use App\Tests\Support\StyleColorScanner;
use PHPUnit\Framework\TestCase;

/** US-205: the partial of the "Technical proof" page follows the rules of the home (tokens only, 44 px targets, no fixed width). */
final class ProofStylesTest extends TestCase
{
    private const STYLES = __DIR__ . '/../../../assets/shop/styles';

    public function testThePartialExistsAndIsImportedByTheB2bStylesheet(): void
    {
        self::assertFileExists(self::STYLES . '/_proof.scss');
        self::assertStringContainsString("@use 'proof';", $this->read('b2b.scss'));
    }

    public function testThePartialWritesNoColourByHand(): void
    {
        self::assertSame([], StyleColorScanner::hardCoded($this->read('_proof.scss')));
    }

    public function testLinksKeepA44PixelTarget(): void
    {
        self::assertGreaterThanOrEqual(2, substr_count($this->read('_proof.scss'), 'min-height: 2.75rem'), 'chips and value links');
    }

    public function testNoTransitionOrAnimationWithoutTheReducedMotionGuard(): void
    {
        $styles = $this->read('_proof.scss');

        if (1 === preg_match('/\b(?:transition|animation)\s*:/', $styles)) {
            self::assertStringContainsString('prefers-reduced-motion', $styles);
        }
        self::assertSame(0, preg_match('/(?<!min-|max-)width\s*:\s*\d+(?:\.\d+)?px/', $styles), 'no fixed width in pixels: 320 px has no horizontal scroll');
    }

    public function testTheTemplateNoLongerCarriesInlineStyles(): void
    {
        $template = (string) file_get_contents(self::STYLES . '/../../../templates/demo/proof.html.twig');

        self::assertStringNotContainsString('<style', $template);
    }

    private function read(string $file): string
    {
        return (string) file_get_contents(self::STYLES . '/' . $file);
    }
}
