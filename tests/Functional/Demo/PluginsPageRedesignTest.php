<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Plugin\DemoPlugin;
use App\Demo\Proof\ProofReader;
use App\Tests\Support\DemoCatalogs;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-204: the public "Plugins" page continues the editorial home (validated mock-up, decision of 2026-10-27): cards
 * grouped by family with name, role, badges drawn from the proof file and links to the code; the ERP plugin is not listed.
 */
final class PluginsPageRedesignTest extends WebTestCase
{
    private const FAMILIES = ['purchasing', 'billing', 'catalog', 'integration', 'compliance', 'admin'];

    private const UNLISTED = 'sylius-erp-flatfile-plugin';

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
        self::assertGreaterThan(0, $page->filter('.b2b-home-wrap')->count());
        self::assertGreaterThanOrEqual(\count(self::FAMILIES), $page->filter('section.b2b-home-section')->count());
        self::assertGreaterThan(0, $page->filter('.b2b-home-eyebrow')->count());
        self::assertCount(0, $page->filter('[data-plugin] .card-body'), 'no more raw Bootstrap cards');
    }

    #[DataProvider('locales')]
    public function testTheFamiliesFollowTheCatalogueOrderAndEachHoldsItsOwnPlugins(string $locale): void
    {
        $page = $this->page($locale);
        $expected = array_keys(DemoCatalogs::plugins()->listedByCategory());

        $found = $page->filter('[data-plugin-category]')->each(static fn (Crawler $section): string => (string) $section->attr('data-plugin-category'));
        self::assertSame($expected, $found);
        foreach (DemoCatalogs::plugins()->listedByCategory() as $family => $plugins) {
            $section = $page->filter('section[data-plugin-category="' . $family . '"]');
            self::assertCount(1, $section->filter('h2'), $family);
            self::assertSame(
                array_map(static fn (DemoPlugin $plugin): string => $plugin->slug, $plugins),
                $section->filter('[data-plugin]')->each(static fn (Crawler $card): string => (string) $card->attr('data-plugin')),
                $family,
            );
        }
        self::assertCount(\count($expected), $page->filter('nav[data-plugins-families] a[href^="#famille-"]'));
        foreach ($expected as $family) {
            self::assertCount(1, $page->filter('section#famille-' . $family . '[data-plugin-category]'), $family);
        }
    }

    #[DataProvider('locales')]
    public function testEveryListedPluginHasANameARoleBadgesAndLinksToTheCode(string $locale): void
    {
        $page = $this->page($locale);
        $proof = $this->proof();

        foreach (DemoCatalogs::plugins()->listed() as $plugin) {
            $card = $page->filter('[data-plugin="' . $plugin->slug . '"]');
            self::assertCount(1, $card, $plugin->slug);
            self::assertSame($plugin->name, trim($card->filter('h3')->text()), $plugin->slug);
            self::assertSame($plugin->description($locale), trim($card->filter('[data-plugin-role]')->text()), $plugin->slug);
            self::assertCount(1, $card->filter('[data-plugin-links] a[href="' . $plugin->githubUrl() . '"]'), $plugin->slug);
            self::assertCount(1, $card->filter('[data-plugin-links] a[data-plugin-packagist]'), $plugin->slug);
            self::assertCount(1, $card->filter('[data-plugin-details]'), $plugin->slug . ': scenario and advised account stay reachable');
            self::assertStringContainsString($plugin->advisedAccount, $card->filter('[data-plugin-account]')->text(), $plugin->slug);

            $version = $proof->fact($plugin->slug, 'version');
            self::assertSame($version->isVerified() ? 1 : 0, $card->filter('[data-plugin-badge="version"]')->count(), $plugin->slug);
            if ($version->isVerified()) {
                self::assertStringContainsString((string) $version->value, $card->filter('[data-plugin-badge="version"]')->text(), $plugin->slug);
            }
            $ci = $proof->fact($plugin->slug, 'ci');
            self::assertSame($ci->isVerified() ? 1 : 0, $card->filter('[data-plugin-badge="ci"]')->count(), $plugin->slug);
            if ($ci->isVerified()) {
                self::assertSame((string) $ci->value, $card->filter('[data-plugin-badge="ci"]')->attr('data-plugin-ci'), $plugin->slug . ': shown as the proof gives it');
            }
            $compatible = $proof->fact($plugin->slug, 'sylius_23');
            self::assertSame($compatible->isVerified() && true === $compatible->value ? 1 : 0, $card->filter('[data-plugin-badge="sylius"]')->count(), $plugin->slug);
        }
    }

    #[DataProvider('locales')]
    public function testTheErpPluginIsNotListedAndNoFigureContradictsTheHome(string $locale): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');
        $page = $client->request('GET', '/' . $locale . '/plugins');

        self::assertCount(0, $page->filter('[data-plugin="' . self::UNLISTED . '"], #plugin-' . self::UNLISTED));
        self::assertStringNotContainsString(self::UNLISTED, (string) $client->getResponse()->getContent());
        self::assertSame(0, preg_match('/\d/', $page->filter('[data-plugins-intro]')->text()), 'the introduction carries no count of plugins');
    }

    #[DataProvider('locales')]
    public function testTheCompatibilityNoteAndTheNeutralToneAreKept(string $locale): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');
        $page = $client->request('GET', '/' . $locale . '/plugins');

        self::assertCount(1, $page->filter('[data-plugins-compat-note]'));
        self::assertCount(1, $page->filter('a[data-plugins-integrate-link]'));
        self::assertCount(1, $page->filter('a[data-plugins-proof-link]'));
        self::assertCount(1, $page->filter('a[data-plugins-compat-link]'));
        $content = (string) $client->getResponse()->getContent();
        self::assertSame(0, preg_match('/sylius\s+plus|concurren|competitor|alternative|\bfree\b|gratuit/i', strip_tags($content)));
    }

    #[DataProvider('locales')]
    public function testEveryTextIsTranslated(string $locale): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');
        $client->request('GET', '/' . $locale . '/plugins');
        $text = strip_tags((string) $client->getResponse()->getContent());

        self::assertSame(0, preg_match('/\b(?:app\.plugins|plugins)\.[a-z_]+\.[a-z_]+/', $text), 'a translation key is shown as is');
        if ('en_US' === $locale) {
            self::assertStringNotContainsString('Scénario', $text);
            self::assertStringNotContainsString('Compte conseillé', $text);
            self::assertStringNotContainsString('Code sur GitHub', $text);
        }
    }

    public function testTheMcpGuideLinkAndTheSimulatorLinkSurvive(): void
    {
        $page = $this->page('fr_FR');

        self::assertCount(1, $page->filter('[data-plugin="sylius-b2b-mcp-plugin"] a[data-plugin-guide-link]'));
        self::assertGreaterThan(0, $page->filter('[data-plugin-demo-link]')->count());
    }

    private function page(string $locale): Crawler
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');
        $page = $client->request('GET', '/' . $locale . '/plugins');
        self::assertResponseIsSuccessful();

        return $page;
    }

    private function proof(): \App\Demo\Proof\ProofReport
    {
        return (new ProofReader(\dirname(__DIR__, 3) . '/config/demo/proof.json'))->report();
    }
}
