<?php

declare(strict_types=1);

namespace App\Tests\Unit\Architecture;

use App\Tests\Support\TableCodeWrapGuard;
use PHPUnit\Framework\TestCase;

/**
 * US-182: Bootstrap gives `code` the rule `word-wrap: break-word`, which beats the `overflow-wrap: anywhere` inherited
 * from a cell: a long class name widened the packs page to 645 px at 360 px. Every page of templates/demo with `code` in
 * a table declares `overflow-wrap: anywhere` for these `code`, outside any media query (the overflow exists at 1280 px too).
 */
final class TableCodeWrapGuardTest extends TestCase
{
    private const TABLE = '<table><tr><td><code>{{ value }}</code></td></tr></table>';

    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/table-code-' . bin2hex(random_bytes(4));
        mkdir($this->directory . '/sub', 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/{,sub/}*.twig', \GLOB_BRACE) ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->directory . '/sub');
        rmdir($this->directory);
    }

    public function testEveryPageOfTheDemoWithCodeInATableWrapsIt(): void
    {
        $global = (string) file_get_contents(\dirname(__DIR__, 3) . '/assets/shop/styles/b2b.scss');

        self::assertSame([], TableCodeWrapGuard::offenders(\dirname(__DIR__, 3) . '/templates/demo', $global), 'Add `code { overflow-wrap: anywhere; }` outside a media query in the <style> of the page.');
    }

    public function testAPageWithTheRuleOutsideAMediaQueryIsCompliant(): void
    {
        $this->page('ok.html.twig', '<style>[data-t] code { overflow-wrap: anywhere; } @media (max-width: 767px) { td { display: block; } }</style>' . self::TABLE);

        self::assertSame([], TableCodeWrapGuard::offenders($this->directory, ''));
    }

    public function testAPageWhoseTablesHaveNoCodeIsNotConcerned(): void
    {
        $this->page('plain.html.twig', '<style>td { padding: 0; }</style><table><tr><td>{{ value }}</td></tr></table><p><code>outside a table</code></p>');

        self::assertSame([], TableCodeWrapGuard::offenders($this->directory, ''));
    }

    public function testACommonRuleCoversThePagesThatInheritTheLayout(): void
    {
        $this->page('inherits.html.twig', "{% extends 'layout.html.twig' %}" . self::TABLE);
        $this->page('standalone.html.twig', self::TABLE);

        self::assertSame(['standalone.html.twig'], TableCodeWrapGuard::offenders($this->directory, 'body { color: red; } code { overflow-wrap: anywhere; }'));
    }

    public function testAPageWithoutTheRuleIsNamed(): void
    {
        $this->page('sub/missing.html.twig', '<style>code { background: #eee; }</style>' . self::TABLE);

        self::assertSame(['sub/missing.html.twig'], TableCodeWrapGuard::offenders($this->directory, ''));
    }

    public function testARuleOnlyInAMediaQueryDoesNotCount(): void
    {
        $this->page('mobile.html.twig', '<style>@media (max-width: 767px) { code { overflow-wrap: anywhere; } }</style>' . self::TABLE);

        self::assertSame(['mobile.html.twig'], TableCodeWrapGuard::offenders($this->directory, ''));
    }

    public function testARuleOnTheCellDoesNotCountBecauseBootstrapOverridesIt(): void
    {
        $this->page('cell.html.twig', '<style>td { overflow-wrap: anywhere; }</style>' . self::TABLE);

        self::assertSame(['cell.html.twig'], TableCodeWrapGuard::offenders($this->directory, ''));
    }

    public function testCodeProducedByAMacroOrAnIncludedPartialInATableIsSeen(): void
    {
        $this->page('macro.html.twig', '{% macro list(v) %}<code>{{ v }}</code>{% endmacro %}{% import _self as page %}<table><tr><td>{{ page.list(v) }}</td></tr></table>');
        $this->page('_cell.html.twig', '<code>{{ v }}</code>');
        $this->page('include.html.twig', "<table><tr><td>{% include '_cell.html.twig' %}</td></tr></table>");

        self::assertSame(['include.html.twig', 'macro.html.twig'], TableCodeWrapGuard::offenders($this->directory, ''));
    }

    public function testATwigCommentIsNotCode(): void
    {
        $this->page('comment.html.twig', '<table><tr><td>{# <code>x</code> #}{{ v }}</td></tr></table>');

        self::assertSame([], TableCodeWrapGuard::offenders($this->directory, ''));
    }

    private function page(string $name, string $content): void
    {
        file_put_contents($this->directory . '/' . $name, $content);
    }
}
