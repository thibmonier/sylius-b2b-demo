<?php

declare(strict_types=1);

namespace App\Tests\Unit\Architecture;

use App\Tests\Support\WideTableGuard;
use PHPUnit\Framework\TestCase;

/**
 * T-TECH-27-06 (action 5 of the retrospective of sprint 26): a table of four columns or more on a page of templates/demo fits a phone
 * (360 px, no horizontal scroll of the page): below 768 px its rows become cards (one cell per line, the heading of the column in
 * `data-label`, the header row hidden visually and never `display: none`) or the table scrolls inside its own region.
 * The guard of US-182 covered the `code` of the tables; this one covers the width of the table itself (accounts: 412 px at 360 px).
 */
final class WideTablesOnAPhoneTest extends TestCase
{
    private const WIDE = '<table><thead><tr><th>A</th><th>B</th><th>C</th><th>D</th></tr></thead><tbody><tr><td data-label="A">1</td><td data-label="B">2</td><td data-label="C">3</td><td data-label="D">4</td></tr></tbody></table>';

    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/wide-tables-' . bin2hex(random_bytes(4));
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

    public function testEveryTableOfFourColumnsOrMoreOfTheDemoFitsAPhone(): void
    {
        self::assertSame([], WideTableGuard::offenders(\dirname(__DIR__, 3) . '/templates/demo'), 'Cards below 768 px (data-label on every cell) or an own scroll region.');
    }

    public function testCardsBelowTheBreakpointAreCompliant(): void
    {
        $this->page('cards.twig', '<style>@media (max-width: 767.98px) { thead { position: absolute; clip: rect(0 0 0 0); } tr { display: block; } td { display: block; } td::before { content: attr(data-label); } }</style>' . self::WIDE);

        self::assertSame([], WideTableGuard::offenders($this->directory));
    }

    public function testAnOwnScrollRegionIsCompliant(): void
    {
        $this->page('scroll.twig', '<div role="region" aria-label="x" tabindex="0" style="overflow-x: auto">' . self::WIDE . '</div>');

        self::assertSame([], WideTableGuard::offenders($this->directory));
    }

    public function testAWideTableWithoutAnyPhoneRuleIsNamed(): void
    {
        $this->page('sub/plain.twig', '<style>table { width: 100%; }</style>' . self::WIDE);

        self::assertSame(['sub/plain.twig'], WideTableGuard::offenders($this->directory));
    }

    public function testACardsRuleOutsideAMediaQueryDoesNotCount(): void
    {
        $this->page('always.twig', '<style>td { display: block; }</style>' . self::WIDE);

        self::assertSame(['always.twig'], WideTableGuard::offenders($this->directory));
    }

    public function testAHeaderRowHiddenWithDisplayNoneIsRefused(): void
    {
        $this->page('hidden.twig', '<style>@media (max-width: 767px) { thead { display: none; } td { display: block; } }</style>' . self::WIDE);

        self::assertSame(['hidden.twig'], WideTableGuard::offenders($this->directory));
    }

    public function testACellWithoutItsLabelIsNamed(): void
    {
        $this->page('nolabel.twig', '<style>@media (max-width: 767px) { thead { clip: rect(0 0 0 0); } td { display: block; } td::before { content: attr(data-label); } }</style>' . str_replace(' data-label="C"', '', self::WIDE));

        self::assertSame(['nolabel.twig'], WideTableGuard::offenders($this->directory));
    }

    public function testATableOfThreeColumnsIsNotConcerned(): void
    {
        $this->page('narrow.twig', '<table><thead><tr><th>A</th><th>B</th><th>C</th></tr></thead><tbody><tr><td>1</td><td>2</td><td>3</td></tr></tbody></table>');

        self::assertSame([], WideTableGuard::offenders($this->directory));
    }

    public function testTheColumnsOfALoopAreCounted(): void
    {
        $this->page('loop.twig', "<table><thead><tr>{% for column in ['a', 'b', 'c', 'd', 'e'] %}<th>{{ column }}</th>{% endfor %}</tr></thead><tbody><tr><td>1</td></tr></tbody></table>");

        self::assertSame(['loop.twig'], WideTableGuard::offenders($this->directory));
    }

    private function page(string $name, string $content): void
    {
        file_put_contents($this->directory . '/' . $name, $content);
    }
}
