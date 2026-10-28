<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-206: the public "Architecture" page continues the editorial home (validated mock-up, decision of 2026-10-28):
 * introduction, table of contents of six anchors, numbered sections each with a visual. Contents and facts are unchanged
 * (ArchitecturePageTest); the markup stays readable on the B2C channel, which does not load the B2B stylesheet.
 */
final class ArchitecturePageRedesignTest extends WebTestCase
{
    private const SECTIONS = ['overview', 'layers', 'flows', 'operations', 'quality', 'security'];

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR'];
        yield 'English' => ['en_US'];
    }

    #[DataProvider('locales')]
    public function testThePageUsesTheClassesOfTheHome(string $locale): void
    {
        $page = $this->page($locale);

        self::assertCount(1, $page->filter('h1.b2b-home-h1'));
        self::assertCount(1, $page->filter('.b2b-home-eyebrow'));
        self::assertGreaterThan(0, $page->filter('.b2b-home-wrap')->count());
        self::assertGreaterThanOrEqual(\count(self::SECTIONS), $page->filter('section.b2b-home-section')->count());
        self::assertCount(1, $page->filter('p.b2b-home-lead'), 'the short introduction');
    }

    #[DataProvider('locales')]
    public function testTheTableOfContentsHasOneAnchorPerSectionInOrder(string $locale): void
    {
        $page = $this->page($locale);
        $anchors = $page->filter('nav[data-architecture-toc] a');

        self::assertCount(\count(self::SECTIONS), $anchors);
        self::assertNotSame('', trim((string) $page->filter('nav[data-architecture-toc]')->attr('aria-label')));
        $targets = $anchors->each(static fn (Crawler $anchor): string => (string) $anchor->attr('href'));
        $sections = $page->filter('section[data-architecture-section]')->each(static fn (Crawler $section): string => '#' . $section->attr('id'));
        self::assertSame($sections, $targets, 'each anchor points to the section of the same rank');
        foreach ($sections as $index => $target) {
            $section = $page->filter('section' . $target . '[data-architecture-section="' . self::SECTIONS[$index] . '"]');
            self::assertCount(1, $section, $target);
            self::assertSame(trim($section->filter('h2')->text()), trim($anchors->eq($index)->text()), 'the anchor is labelled by the title of its section');
        }
    }

    /** Keyboard: the target of an anchor takes the focus and announces its title, and nothing traps it. */
    #[DataProvider('locales')]
    public function testTheTitleOfEachSectionCanTakeTheFocusFromItsAnchor(string $locale): void
    {
        $page = $this->page($locale);

        foreach ($page->filter('section[data-architecture-section]') as $node) {
            $section = new Crawler($node);
            $title = $section->filter('h2');
            self::assertCount(1, $title);
            self::assertSame('-1', $title->attr('tabindex'), 'focusable by script, out of the tab order');
            self::assertSame($title->attr('id'), $section->attr('aria-labelledby'), 'the section is announced by its title');
        }
        self::assertCount(0, $page->filter('[tabindex]:not([tabindex="-1"]):not([tabindex="0"])'), 'no positive tabindex');
    }

    #[DataProvider('locales')]
    public function testEachSectionHasOneVisualAndTheBlocksAreCounted(string $locale): void
    {
        $page = $this->page($locale);

        foreach (self::SECTIONS as $name) {
            self::assertCount(1, $page->filter('section[data-architecture-section="' . $name . '"] [data-architecture-visual]'), $name);
        }
        self::assertCount(4, $page->filter('[data-architecture-figure]'), 'plugins, tests, Sylius, Symfony');
        self::assertCount(4, $page->filter('[data-architecture-layer]'), 'core, plugins, demo, theme');
        self::assertCount(5, $page->filter('[data-architecture-flow]'), 'events, webhooks, MCP, PunchOut, invoice');
        self::assertCount(6, $page->filter('[data-architecture-step]'), 'the end-to-end example');
        self::assertCount(4, $page->filter('[data-architecture-operation]'), 'image, deployment, reset, public');
        self::assertCount(2 + $page->filter('[data-architecture-quality-tile][data-architecture-ci]')->count(), $page->filter('[data-architecture-quality-tile]'), 'PHPStan level, test files, and the CI tile only with a verified run of the repository');
        self::assertCount(6, $page->filter('[data-architecture-principle]'), 'the six general security principles');
    }

    /** The numbers of the sections, of the anchors and of the steps are drawn by the stylesheet: no digit reaches the text. */
    #[DataProvider('locales')]
    public function testNumberingIsDrawnByTheStylesheetAndNotWrittenInTheMarkup(string $locale): void
    {
        $page = $this->page($locale);

        self::assertSame(0, preg_match('/\d/', $page->filter('nav[data-architecture-toc]')->text()), 'no digit in the table of contents');
        foreach ($page->filter('section[data-architecture-section] h2') as $title) {
            self::assertSame(0, preg_match('/^\s*\d/', (new Crawler($title))->text()));
        }
        foreach ($page->filter('[data-architecture-step]') as $step) {
            self::assertSame(0, preg_match('/^\s*\d/', (new Crawler($step))->text()));
        }
    }

    public function testTheTextsAreTranslated(): void
    {
        $french = $this->page('fr_FR');
        $english = $this->page('en_US');

        self::assertNotSame($french->filter('h1')->text(), $english->filter('h1')->text());
        self::assertNotSame($french->filter('nav[data-architecture-toc]')->text(), $english->filter('nav[data-architecture-toc]')->text());
        self::assertNotSame($french->filter('p.b2b-home-lead')->text(), $english->filter('p.b2b-home-lead')->text());
        self::assertDoesNotMatchRegularExpression('/architecture\.[a-z_.]+/i', $english->filter('main')->text() . $french->filter('main')->text(), 'no translation key shown');
        self::assertSame(
            $french->filter('section[data-architecture-section]')->count(),
            $english->filter('section[data-architecture-section]')->count(),
        );
    }

    #[DataProvider('locales')]
    public function testTheLinksToTheRepositoryOpenInANewTabAndSayIt(string $locale): void
    {
        $links = $this->page($locale)->filter('[data-architecture-links] a');

        self::assertGreaterThan(0, $links->count());
        foreach ($links as $link) {
            $link = new Crawler($link);
            self::assertSame('_blank', $link->attr('target'));
            self::assertStringContainsString('noopener', (string) $link->attr('rel'));
        }
    }

    /** The B2C channel does not load the B2B stylesheet: the page answers and its markup stays readable there. */
    #[DataProvider('locales')]
    public function testThePageStillAnswersOnTheB2cChannel(string $locale): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2C');
        $page = $client->request('GET', '/' . $locale . '/architecture');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $page->filter('h1'));
        self::assertCount(\count(self::SECTIONS), $page->filter('section[data-architecture-section]'));
        self::assertCount(\count(self::SECTIONS), $page->filter('nav[data-architecture-toc] a'));
    }

    private function page(string $locale): Crawler
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');
        $page = $client->request('GET', '/' . $locale . '/architecture');
        self::assertResponseIsSuccessful();

        return $page;
    }
}
