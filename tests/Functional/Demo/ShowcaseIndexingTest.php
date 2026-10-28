<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Channel\DemoChannelContext;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\RouterInterface;

/**
 * US-207: the showcase pages (and only they) are indexable once the indexing setting is on. Everything else keeps
 * `noindex, nofollow` (refusal by default), the private mode stays closed, and the sitemap lists the whitelist and
 * nothing more. The configuration is set by each test; the rendered answers are what is asserted.
 */
final class ShowcaseIndexingTest extends WebTestCase
{
    use DatabaseAccess;
    use EnvironmentSwitch;

    private const HOST = 'https://showcase.example';

    private const PROFILE = 'https://www.linkedin.com/in/example-author/';

    /** Independent oracle: the whitelist as the story fixes it (page path without the locale). */
    private const SHOWCASE = ['/', '/plugins', '/architecture', '/preuve', '/a-propos', '/integrer', '/mcp', '/surcharge', '/compatibilite', '/packs'];

    private const LOCALES = ['fr_FR' => 'fr-FR', 'en_US' => 'en-US'];

    /** @return iterable<string, array{string, string}> */
    public static function showcasePages(): iterable
    {
        foreach (array_keys(self::LOCALES) as $locale) {
            foreach (self::SHOWCASE as $path) {
                yield $locale . ' ' . $path => [$locale, '/' . $locale . $path];
            }
        }
    }

    /** @return iterable<string, array{string}> */
    public static function privatePaths(): iterable
    {
        foreach (['/fr_FR/taxons/visserie', '/en_US/taxons/visserie', '/fr_FR/login', '/fr_FR/cart/', '/fr_FR/account/', '/fr_FR/guides', '/fr_FR/mentions-legales', '/fr_FR/credits-images', '/fr_FR/pa-fictive', '/fr_FR/echange-fichier', '/demo/accounts', '/demo/mails'] as $path) {
            yield $path => [$path];
        }
    }

    #[DataProvider('showcasePages')]
    public function testAShowcasePageIsIndexableWithItsOwnMetadataWhenTheSettingIsOn(string $locale, string $path): void
    {
        $client = $this->indexingClient();
        $page = $client->request('GET', $path);

        self::assertResponseIsSuccessful($path);
        self::assertFalse($client->getResponse()->headers->has('X-Robots-Tag'), 'No noindex header on ' . $path);
        self::assertCount(0, $page->filter('meta[name="robots"][content*="noindex"]'));
        self::assertSame(self::HOST . $path, $page->filter('link[rel="canonical"]')->attr('href'));
        self::assertCount(1, $page->filter('meta[name="description"]'));

        $alternates = $this->alternates($page);
        $slug = (string) preg_replace('#^/[a-z]{2}_[A-Z]{2}#', '', $path);
        self::assertSame(
            ['en-US' => self::HOST . '/en_US' . $slug, 'fr-FR' => self::HOST . '/fr_FR' . $slug, 'x-default' => self::HOST . '/fr_FR' . $slug],
            $alternates,
        );
    }

    #[DataProvider('showcasePages')]
    public function testTheTitleOfAShowcasePageIsShortAndNamesTheSite(string $locale, string $path): void
    {
        $page = $this->indexingClient()->request('GET', $path);

        $title = trim($page->filter('title')->text());
        self::assertLessThanOrEqual(65, mb_strlen($title), $title);
        self::assertStringContainsStringIgnoringCase('sylius', $title);
        self::assertSame($title, (string) $page->filter('meta[property="og:title"]')->attr('content'));
    }

    #[DataProvider('showcasePages')]
    public function testTheTextsOfAShowcasePageStayNeutral(string $locale, string $path): void
    {
        $page = $this->indexingClient()->request('GET', $path);

        $texts = $page->filter('title')->text() . ' ' . $page->filter('meta[name="description"]')->attr('content') . ' ' . implode(' ', $this->jsonLd($page));
        self::assertDoesNotMatchRegularExpression('/sylius\s+plus|\bversus\b|\bvs\.?\s|enterprise|moins cher|cheaper|better than|meilleur que/i', $texts);
    }

    public function testThePluginsAndArchitectureTitlesFollowTheSearchIntent(): void
    {
        $client = $this->indexingClient();

        $plugins = ['fr_FR' => $client->request('GET', '/fr_FR/plugins')->filter('title')->text(), 'en_US' => $client->request('GET', '/en_US/plugins')->filter('title')->text()];
        $architecture = ['fr_FR' => $client->request('GET', '/fr_FR/architecture')->filter('title')->text(), 'en_US' => $client->request('GET', '/en_US/architecture')->filter('title')->text()];
        foreach ($plugins as $title) {
            self::assertMatchesRegularExpression('/plugins/i', $title);
            self::assertMatchesRegularExpression('/B2B/', $title);
        }
        foreach ($architecture as $title) {
            self::assertMatchesRegularExpression('/architecture/i', $title);
            self::assertStringContainsString('Sylius 2', $title);
        }
    }

    #[DataProvider('locales')]
    public function testTheTitlesAndDescriptionsOfTheShowcaseDifferFromPageToPage(string $locale): void
    {
        $client = $this->indexingClient();
        $titles = [];
        $descriptions = [];
        foreach (self::SHOWCASE as $path) {
            $page = $client->request('GET', '/' . $locale . $path);
            $titles[] = trim($page->filter('title')->text());
            $descriptions[] = (string) $page->filter('meta[name="description"]')->attr('content');
        }

        self::assertCount(\count(self::SHOWCASE), array_unique($titles), 'One title per page.');
        self::assertCount(\count(self::SHOWCASE), array_unique($descriptions), 'One description per page.');
    }

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        foreach (array_keys(self::LOCALES) as $locale) {
            yield $locale => [$locale];
        }
    }

    #[DataProvider('showcasePages')]
    public function testEverythingStaysNoindexWhenTheSettingIsOff(string $locale, string $path): void
    {
        $this->publicMode(false);
        $client = self::createClient();

        $page = $client->request('GET', $path);

        self::assertResponseIsSuccessful($path);
        self::assertResponseHeaderSame('X-Robots-Tag', 'noindex, nofollow');
        self::assertCount(0, $page->filter('link[rel="alternate"][hreflang]'));
        self::assertCount(0, $page->filter('script[type="application/ld+json"]'));
    }

    public function testTheSitemapAndTheIndexingLineAreAbsentWhenTheSettingIsOff(): void
    {
        $this->publicMode(false);
        $client = self::createClient();

        $client->request('GET', '/sitemap.xml');
        self::assertResponseStatusCodeSame(404);

        $client->request('GET', '/robots.txt');
        self::assertStringNotContainsString('Sitemap:', (string) $client->getResponse()->getContent());
        self::assertMatchesRegularExpression('/^User-agent: \*\R+Disallow: \/\s*$/m', (string) $client->getResponse()->getContent());
    }

    #[DataProvider('privatePaths')]
    public function testEverythingOutsideTheWhitelistStaysNoindexWhenTheSettingIsOn(string $path): void
    {
        $client = $this->indexingClient();

        $client->request('GET', $path);

        self::assertResponseHeaderSame('X-Robots-Tag', 'noindex, nofollow');
    }

    public function testAProductPageStaysNoindexWhenTheSettingIsOn(): void
    {
        $client = $this->indexingClient();
        $slug = $this->stringValue("SELECT t.slug FROM sylius_product_translation t JOIN sylius_product p ON p.id = t.translatable_id WHERE p.code = 'VISS-001' AND t.locale = ?", ['fr_FR']);

        $client->request('GET', '/fr_FR/products/' . $slug);

        self::assertResponseHeaderSame('X-Robots-Tag', 'noindex, nofollow');
    }

    public function testTheConsumerChannelStaysNoindexWhenTheSettingIsOn(): void
    {
        $client = $this->indexingClient();
        $client->getCookieJar()->set(new Cookie(DemoChannelContext::COOKIE, DemoChannelContext::B2C));

        $client->request('GET', '/fr_FR/plugins');

        self::assertResponseHeaderSame('X-Robots-Tag', 'noindex, nofollow');
    }

    public function testThePrivateModeStaysClosedWhateverTheSetting(): void
    {
        $this->setEnvironment(['DEMO_INDEXING' => '1', 'DEMO_BASIC_AUTH' => '1', 'DEMO_BASIC_AUTH_USER' => 'visitor', 'DEMO_BASIC_AUTH_PASSWORD_HASH' => password_hash('correct horse battery', \PASSWORD_DEFAULT)]);
        $client = self::createClient();
        $credentials = ['PHP_AUTH_USER' => 'visitor', 'PHP_AUTH_PW' => 'correct horse battery'];

        $client->request('GET', '/fr_FR/plugins');
        self::assertResponseStatusCodeSame(401);
        self::assertResponseHeaderSame('X-Robots-Tag', 'noindex, nofollow');

        $client->request('GET', '/fr_FR/plugins', server: $credentials);
        self::assertResponseHeaderSame('X-Robots-Tag', 'noindex, nofollow');

        $client->request('GET', '/robots.txt', server: $credentials);
        self::assertMatchesRegularExpression('/^User-agent: \*\R+Disallow: \/\s*$/m', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Sitemap:', (string) $client->getResponse()->getContent());

        $client->request('GET', '/sitemap.xml', server: $credentials);
        self::assertResponseStatusCodeSame(404);
    }

    public function testRobotsTxtExcludesTheZonesWithoutInterestAndPointsToTheSitemap(): void
    {
        $client = $this->indexingClient();

        $client->request('GET', '/robots.txt');

        self::assertResponseIsSuccessful();
        $body = (string) $client->getResponse()->getContent();
        foreach (['/admin', '/demo', '/api', '/cart', '/checkout', '/account', 'sorting'] as $zone) {
            self::assertStringContainsString($zone, $body);
        }
        self::assertDoesNotMatchRegularExpression('/^Disallow: \/\s*$/m', $body, 'The site is no longer disallowed as a whole.');
        self::assertStringContainsString('Sitemap: ' . self::HOST . '/sitemap.xml', $body);
    }

    public function testTheSitemapListsTheWhitelistWithTheAlternatesOfEachLanguage(): void
    {
        $client = $this->indexingClient();

        $client->request('GET', '/sitemap.xml');

        self::assertResponseIsSuccessful();
        self::assertStringStartsWith('application/xml', (string) $client->getResponse()->headers->get('Content-Type'));
        $xml = simplexml_load_string((string) $client->getResponse()->getContent());
        self::assertNotFalse($xml);
        $xml->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $xml->registerXPathNamespace('x', 'http://www.w3.org/1999/xhtml');

        $expected = [];
        foreach (self::SHOWCASE as $path) {
            foreach (array_keys(self::LOCALES) as $locale) {
                $expected[] = self::HOST . '/' . $locale . $path;
            }
        }
        $locs = array_map('strval', $xml->xpath('//s:url/s:loc') ?: []);
        sort($expected);
        sort($locs);
        self::assertSame($expected, $locs, 'Exactly the whitelist, once per language.');

        foreach ($xml->xpath('//s:url') ?: [] as $url) {
            $url->registerXPathNamespace('x', 'http://www.w3.org/1999/xhtml');
            $hreflangs = array_map('strval', $url->xpath('x:link[@rel="alternate"]/@hreflang') ?: []);
            sort($hreflangs);
            self::assertSame(['en-US', 'fr-FR', 'x-default'], $hreflangs, (string) $url->loc);
        }
    }

    public function testNoRouteOutsideTheWhitelistAppearsInTheSitemap(): void
    {
        $client = $this->indexingClient();
        $client->request('GET', '/sitemap.xml');
        $xml = simplexml_load_string((string) $client->getResponse()->getContent());
        self::assertNotFalse($xml);
        $xml->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $paths = array_map(static fn ($loc): string => (string) parse_url((string) $loc, \PHP_URL_PATH), $xml->xpath('//s:url/s:loc') ?: []);
        self::assertNotSame([], $paths, 'The sitemap is not empty.');

        $router = self::getContainer()->get('router');
        self::assertInstanceOf(RouterInterface::class, $router);
        $whitelist = self::getContainer()->getParameter('demo.seo.indexable_routes');
        self::assertIsArray($whitelist);
        foreach ($paths as $path) {
            $route = $router->match($path)['_route'] ?? null;
            self::assertContains($route, $whitelist, $path . ' is not a whitelisted route.');
        }
    }

    public function testTheWhitelistIsTheOneTheStoryFixes(): void
    {
        $this->indexingClient();

        $whitelist = self::getContainer()->getParameter('demo.seo.indexable_routes');
        self::assertIsArray($whitelist);
        $expected = ['sylius_shop_homepage', 'app_demo_plugins', 'app_demo_architecture', 'app_demo_proof', 'app_demo_about', 'app_demo_integrate', 'app_demo_mcp_guide', 'app_demo_override_guide', 'app_demo_compatibility', 'app_demo_packs'];
        sort($expected);
        sort($whitelist);
        self::assertSame($expected, $whitelist);
    }

    public function testTheHomeDescribesTheWebsiteAndItsAuthor(): void
    {
        $client = $this->indexingClient();

        $types = $this->types($client->request('GET', '/fr_FR/'));

        self::assertContains('WebSite', $types);
        self::assertContains('Person', $types);
    }

    public function testTheAboutPageDescribesItsAuthor(): void
    {
        $page = $this->indexingClient()->request('GET', '/en_US/a-propos');

        $person = $this->nodesOfType($page, 'Person');
        self::assertCount(1, $person);
        self::assertSame('Thibaut Monier', $person[0]['name'] ?? null);
        self::assertSame(self::PROFILE, $person[0]['sameAs'] ?? null);
    }

    #[DataProvider('pagesWithRepositories')]
    public function testPluginsAndArchitectureDescribeVerifiableRepositories(string $path): void
    {
        $page = $this->indexingClient()->request('GET', $path);

        $repositories = $this->nodesOfType($page, 'SoftwareSourceCode');
        self::assertNotSame([], $repositories);
        foreach ($repositories as $repository) {
            self::assertMatchesRegularExpression('#^https://github\.com/thibmonier/[a-z0-9-]+$#', (string) ($repository['codeRepository'] ?? ''));
        }
    }

    /** @return iterable<string, array{string}> */
    public static function pagesWithRepositories(): iterable
    {
        foreach (['/fr_FR/plugins', '/en_US/plugins', '/fr_FR/architecture', '/en_US/architecture'] as $path) {
            yield $path => [$path];
        }
    }

    public function testThePluginsPageDescribesOnlyThePluginsTheCatalogueAllowsAndNoRating(): void
    {
        $client = $this->indexingClient();
        $entries = self::getContainer()->getParameter('demo.plugin_catalog');
        self::assertIsArray($entries);
        $excluded = array_values(array_filter($entries, static fn (array $entry): bool => false === ($entry['structured_data'] ?? true)));
        $allowed = array_values(array_filter($entries, static fn (array $entry): bool => false !== ($entry['listed'] ?? true) && false !== ($entry['structured_data'] ?? true)));
        self::assertNotSame([], $excluded, 'The catalogue flags the plugins left out of the structured data.');

        $page = $client->request('GET', '/fr_FR/plugins');
        $repositories = array_map(static fn (array $node): string => (string) ($node['codeRepository'] ?? ''), $this->nodesOfType($page, 'SoftwareSourceCode'));
        self::assertCount(\count($allowed), $repositories);
        foreach ($excluded as $entry) {
            self::assertNotContains('https://github.com/thibmonier/' . $entry['slug'], $repositories);
            foreach ($this->jsonLd($page) as $block) {
                self::assertStringNotContainsString($entry['slug'], $block);
                self::assertStringNotContainsString((string) $entry['name'], $block);
            }
        }
        foreach ($this->jsonLd($page) as $block) {
            self::assertDoesNotMatchRegularExpression('/"(aggregateRating|offers|review|price)"/', $block);
            self::assertStringNotContainsStringIgnoringCase('erp', $block);
        }
    }

    public function testTheDescriptionsOfTheShowcaseNameNeitherTheFlaggedPluginsNorTheFileExchange(): void
    {
        $client = $this->indexingClient();
        $entries = self::getContainer()->getParameter('demo.plugin_catalog');
        self::assertIsArray($entries);
        $excluded = array_filter($entries, static fn (array $entry): bool => false === ($entry['structured_data'] ?? true));
        self::assertNotSame([], $excluded);

        foreach (['/fr_FR/', '/fr_FR/plugins', '/en_US/plugins'] as $path) {
            $description = mb_strtolower((string) $client->request('GET', $path)->filter('meta[name="description"]')->attr('content'));
            foreach ($excluded as $entry) {
                self::assertStringNotContainsString(mb_strtolower((string) $entry['name']), $description, $path);
            }
            self::assertStringNotContainsString('erp', $description, $path);
        }
    }

    private function indexingClient(): KernelBrowser
    {
        $this->publicMode(true);

        return self::createClient();
    }

    private function publicMode(bool $indexing): void
    {
        $this->setEnvironment([
            'DEMO_BASIC_AUTH' => '0',
            'DEMO_REGISTRATION_OPEN' => '0',
            'DEMO_PUNCHOUT_PUBLIC_ENDPOINT' => '0',
            'DEMO_INDEXING' => $indexing ? '1' : '0',
            'DEFAULT_URI' => self::HOST,
            'DEMO_PROFILE_URL' => self::PROFILE,
        ]);
    }

    /** @return array<string, string> hreflang => address */
    private function alternates(Crawler $page): array
    {
        $alternates = [];
        foreach ($page->filter('link[rel="alternate"][hreflang]') as $link) {
            $alternates[(string) $link->getAttribute('hreflang')] = (string) $link->getAttribute('href');
        }
        ksort($alternates);

        return $alternates;
    }

    /** @return list<string> raw JSON of every structured data block */
    private function jsonLd(Crawler $page): array
    {
        return array_values($page->filter('script[type="application/ld+json"]')->each(static fn (Crawler $node): string => $node->html()));
    }

    /** @return list<array<string, mixed>> every node of the structured data (graph or single object) */
    private function nodes(Crawler $page): array
    {
        $nodes = [];
        foreach ($this->jsonLd($page) as $json) {
            $data = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
            self::assertIsArray($data);
            self::assertSame('https://schema.org', $data['@context'] ?? null);
            foreach ($data['@graph'] ?? [$data] as $node) {
                $nodes[] = $node;
            }
        }

        return $nodes;
    }

    /** @return list<array<string, mixed>> */
    private function nodesOfType(Crawler $page, string $type): array
    {
        return array_values(array_filter($this->nodes($page), static fn (array $node): bool => ($node['@type'] ?? null) === $type));
    }

    /** @return list<string> */
    private function types(Crawler $page): array
    {
        return array_map(static fn (array $node): string => (string) ($node['@type'] ?? ''), $this->nodes($page));
    }
}
