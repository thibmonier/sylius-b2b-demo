<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-192: the public page /demo/accounts fits a phone (360 and 320 px, no horizontal scroll). Below 768 px each row of its
 * tables becomes a card: one cell per line, its column heading (`data-label`) shown before the value, the header row kept for
 * the screen readers (visually hidden, never `display: none`). Same pattern as the pages Compatibility, Proof and Packs.
 */
final class DemoAccountsPageLayoutTest extends WebTestCase
{
    public function testEveryCellOfEveryTableNamesItsColumn(): void
    {
        $page = self::createClient()->request('GET', '/demo/accounts');

        self::assertResponseIsSuccessful();
        self::assertGreaterThanOrEqual(2, $page->filter('table')->count());
        foreach ($page->filter('table') as $table) {
            $table = new Crawler($table);
            $headings = $table->filter('thead th')->each(static fn (Crawler $th): string => trim($th->text()));
            self::assertCount(4, $headings);
            foreach ($table->filter('tbody tr') as $row) {
                $labels = (new Crawler($row))->filter('td')->each(static fn (Crawler $td): string => (string) $td->attr('data-label'));
                self::assertSame($headings, $labels, 'the label of a cell is the text of its column heading');
            }
        }
    }

    public function testTheTablesKeepTheirSemanticsWhenTheyAreStacked(): void
    {
        $page = self::createClient()->request('GET', '/demo/accounts');

        foreach ($page->filter('table') as $table) {
            $table = new Crawler($table);
            self::assertSame('table', $table->attr('role'));
            self::assertCount($table->filter('thead th')->count(), $table->filter('thead th[scope="col"]'));
            self::assertCount($table->filter('tbody tr')->count(), $table->filter('tbody tr[role="row"]'));
            self::assertCount($table->filter('tbody td')->count(), $table->filter('tbody td[role="cell"]'));
        }
    }

    public function testBelowTheBreakpointTheRowsAreCardsAndTheHeaderIsHiddenVisuallyOnly(): void
    {
        $style = $this->style();

        self::assertMatchesRegularExpression('/@media\s*\(max-width:\s*767(?:\.98)?px\)\s*\{[^@]*td[^{]*\{[^}]*display:\s*block/s', $style, 'the cells are stacked on a phone');
        self::assertMatchesRegularExpression('/@media\s*\(max-width:\s*767(?:\.98)?px\)\s*\{[^@]*thead[^{]*\{[^}]*clip:/s', $style, 'the header row is visually hidden');
        self::assertMatchesRegularExpression('/@media[^{]*\{[^@]*td::before[^{]*\{[^}]*content:\s*attr\(data-label\)/s', $style, 'the column heading is shown before the value');
        self::assertDoesNotMatchRegularExpression('/thead[^{]*\{[^}]*display:\s*none/', $style, 'display: none would hide the headings from the screen readers');
    }

    public function testTheLongValuesWrapAtEveryWidth(): void
    {
        $style = preg_replace('/@media[^{]*\{(?:[^{}]*\{[^{}]*\})*[^{}]*\}/s', '', $this->style()) ?? '';

        self::assertMatchesRegularExpression('/code[^{]*\{[^}]*overflow-wrap:\s*anywhere/', $style, 'outside any media query (US-182)');
        self::assertMatchesRegularExpression('/(?:th\s*,\s*td|td)[^{]*\{[^}]*overflow-wrap:\s*anywhere/', $style, 'the cells wrap too, so the table never widens the page');
    }

    private function style(): string
    {
        $client = self::createClient();
        $client->request('GET', '/demo/accounts');

        return implode("\n", (new Crawler((string) $client->getResponse()->getContent()))->filter('style')->each(static fn (Crawler $style): string => $style->text(null, false)));
    }
}
