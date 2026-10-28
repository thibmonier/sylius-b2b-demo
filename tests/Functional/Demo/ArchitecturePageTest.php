<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Plugin\DemoPluginCatalog;
use PHPUnit\Framework\Attributes\DataProvider;
use Sylius\Bundle\CoreBundle\SyliusCoreBundle;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Yaml;

/**
 * US-170: the page "Architecture of the demo": how the demo itself is built (overview diagram, layers, flows,
 * operations, quality, security), in French and English. Every figure comes from proof.json or the configuration,
 * every section links to the public repository, and the security section stays general (no route, value nor threshold).
 */
final class ArchitecturePageTest extends WebTestCase
{
    use EnvironmentSwitch;

    private const REPOSITORY = 'https://github.com/thibmonier/sylius-b2b-demo/';

    private const SECTIONS = ['overview', 'layers', 'flows', 'operations', 'quality', 'security'];

    /** @var list<string> */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR'];
        yield 'English' => ['en_US'];
    }

    #[DataProvider('locales')]
    public function testThePageHasItsSixSectionsEachWithLinksToTheRepository(string $locale): void
    {
        $page = $this->page('/' . $locale . '/architecture');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $page->filter('h1'));
        $sections = $page->filter('section[data-architecture-section]');
        self::assertSame(self::SECTIONS, $sections->each(static fn (Crawler $node): string => (string) $node->attr('data-architecture-section')));
        foreach ($sections as $section) {
            $section = new Crawler($section);
            $name = (string) $section->attr('data-architecture-section');
            self::assertCount(1, $section->filter('h2'), $name);
            self::assertGreaterThan(0, $section->filter('a[href^="' . self::REPOSITORY . '"]')->count(), $name . ' links to the repository');
        }
    }

    /** US-206 (decision of 2026-10-28, mock-up v22): the overview diagram is HTML/CSS and replaces the SVG; the `data-architecture-overview` marker stays. */
    #[DataProvider('locales')]
    public function testTheDiagramHasATitleAndATextualEquivalentAndFollowsTheTheme(string $locale): void
    {
        $page = $this->page('/' . $locale . '/architecture');

        $diagram = $page->filter('figure[data-architecture-overview]');
        self::assertCount(1, $diagram);
        self::assertCount(0, $page->filter('svg[data-architecture-overview]'), 'the SVG is replaced');
        foreach (['aria-labelledby', 'aria-describedby'] as $attribute) {
            self::assertNotSame('', trim($page->filter('#' . $diagram->attr($attribute))->text()), $attribute);
        }
        self::assertGreaterThan(120, mb_strlen($page->filter('#' . $diagram->attr('aria-describedby'))->text()), 'the description gives the diagram to a screen reader');
        self::assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,6}/', $diagram->outerHtml(), 'colours come from the stylesheet and follow the themes');
    }

    /** US-206: the diagram stacks on a phone, so it needs no scroll region any more; every block and link of the old diagram is still there. */
    #[DataProvider('locales')]
    public function testTheDiagramStacksOnAPhoneAndKeepsEveryBlockOfTheOldDiagram(string $locale): void
    {
        $page = $this->page('/' . $locale . '/architecture');
        $diagram = $page->filter('figure[data-architecture-overview]');

        self::assertCount(0, $page->filter('[data-architecture-scroll]'), 'no scroll region: nothing overflows');
        self::assertStringNotContainsString('overflow-auto', $diagram->outerHtml());
        self::assertCount(3, $diagram->filter('[data-architecture-entries] > li'), 'browser, assistant, procurement');
        self::assertCount(5, $diagram->filter('[data-architecture-container] li'), 'Sylius, plugins, demo layer, scheduler, flows');
        self::assertCount(3, $diagram->filter('[data-architecture-storage] > li'), 'database, media, delivery');
        self::assertNotSame('', trim($diagram->filter('figcaption')->text()));
        self::assertCount(3, $page->filter('section[data-architecture-section="overview"] [data-architecture-links] a'), 'Dockerfile, Caddyfile, decision');
    }

    /** US-206: label sizes are written in rem in the stylesheet (never in inline pixels), so the text scales and stays at least 11 CSS pixels. */
    #[DataProvider('locales')]
    public function testTheDiagramTextIsSizedInRemByTheStylesheet(string $locale): void
    {
        $diagram = $this->page('/' . $locale . '/architecture')->filter('figure[data-architecture-overview]');

        self::assertSame(0, preg_match('/font-size\s*:/', $diagram->outerHtml()), 'no inline font size');
        $styles = (string) file_get_contents(\dirname(__DIR__, 3) . '/assets/shop/styles/_architecture.scss');
        self::assertSame(1, preg_match('/\.b2b-architecture-box\s*\{[^}]*font-size:\s*([\d.]+)rem/s', $styles, $size), 'the block label is sized in rem');
        self::assertGreaterThanOrEqual(11.0 / 16, (float) ($size[1] ?? 0), 'at least 11 CSS pixels');
    }

    /** @return iterable<string, array{string, string, string}> */
    public static function linkingPages(): iterable
    {
        foreach (['fr_FR', 'en_US'] as $locale) {
            yield $locale . ' footer' => [$locale, '/' . $locale . '/', '[data-demo-footer] a[href="/' . $locale . '/architecture"]'];
            yield $locale . ' integrate' => [$locale, '/' . $locale . '/integrer', 'section[aria-labelledby="integrate-architecture"] a[data-integrate-architecture-link][href="/' . $locale . '/architecture"]'];
        }
    }

    #[DataProvider('linkingPages')]
    public function testTheFooterAndTheIntegrationGuideLinkToThePage(string $locale, string $path, string $selector): void
    {
        self::assertCount(1, $this->page($path)->filter($selector), $selector);
    }

    #[DataProvider('locales')]
    public function testEveryFigureComesFromTheProofOrTheConfiguration(string $locale): void
    {
        $this->setEnvironment(['DEMO_PROOF_FILE' => $this->file([
            'generated_at' => '2026-10-23T06:57:31Z',
            'plugins' => [],
            'delivery' => [
                'tests' => ['value' => 4242, 'source' => self::REPOSITORY . 'tree/main/tests', 'checked_at' => '2026-10-23'],
                'phpstan' => ['value' => '9', 'source' => self::REPOSITORY . 'blob/main/phpstan.dist.neon', 'checked_at' => '2026-10-23'],
                'ci' => ['value' => 'success', 'source' => self::REPOSITORY . 'actions/runs/1', 'checked_at' => '2026-10-23'],
            ],
        ])]);
        $client = self::createClient();
        $page = $client->request('GET', '/' . $locale . '/architecture');
        $catalog = self::getContainer()->get(DemoPluginCatalog::class);
        self::assertInstanceOf(DemoPluginCatalog::class, $catalog);
        $plugins = (string) \count($catalog->all());

        $text = self::withoutTradeTerms($page->filter('main')->text());
        self::assertStringContainsString('4242', $text);
        self::assertSame($plugins, $page->filter('[data-architecture-figure="plugins"]')->text());
        self::assertSame('4242', $page->filter('[data-architecture-figure="tests"]')->text());
        $allowed = ['4242', $plugins, '9', '2026', '10', '23', SyliusCoreBundle::MAJOR_VERSION . '.' . SyliusCoreBundle::MINOR_VERSION, Kernel::MAJOR_VERSION . '.' . Kernel::MINOR_VERSION, \PHP_MAJOR_VERSION . '.' . \PHP_MINOR_VERSION];
        preg_match_all('/\d+(?:[.,]\d+)*/', $text, $numbers);
        self::assertSame([], array_values(array_diff($numbers[0], $allowed)), 'a number on the page that comes from neither proof.json nor the configuration');
    }

    public function testWithoutAProofFileThePageStillAnswersAndShowsNoTestFigure(): void
    {
        $this->setEnvironment(['DEMO_PROOF_FILE' => sys_get_temp_dir() . '/proof-missing-' . bin2hex(random_bytes(4)) . '.json']);
        $client = self::createClient();
        $page = $client->request('GET', '/fr_FR/architecture');

        self::assertResponseIsSuccessful();
        self::assertDoesNotMatchRegularExpression('/\d/', $page->filter('[data-architecture-figure="tests"]')->text());
    }

    public function testTheTranslationsHoldNoFigure(): void
    {
        foreach (['fr', 'en'] as $language) {
            foreach ($this->texts($language) as $key => $text) {
                self::assertDoesNotMatchRegularExpression('/\d/', self::withoutTradeTerms($text), $language . ': ' . $key . ' writes a figure by hand; pass it as a parameter');
            }
        }
    }

    #[DataProvider('locales')]
    public function testTheSecuritySectionStaysGeneral(string $locale): void
    {
        $section = $this->page('/' . $locale . '/architecture')->filter('section[data-architecture-section="security"]');
        $text = mb_strtolower($section->text());

        self::assertDoesNotMatchRegularExpression('/\d/', $text, 'no value nor threshold');
        self::assertDoesNotMatchRegularExpression('#(^|[\s(«"])/[a-z_]#', $text, 'no route');
        foreach (['per minute', 'par minute', 'requests per', 'requêtes par', 'rate_limiter', 'limiter:', '.yaml', 'basic auth', 'password', 'mot de passe'] as $term) {
            self::assertStringNotContainsString($term, $text, $term);
        }
    }

    public function testThePageHasItsOwnDescription(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $page = $client->request('GET', '/fr_FR/architecture');

        self::assertStringContainsString('architecture', mb_strtolower((string) $page->filter('meta[name="description"]')->attr('content')));
    }

    /** Neutral tone (US-116 charter): no comparison with a commercial offer, no price, in both languages. */
    public function testTheTextsRespectTheEditorialCharter(): void
    {
        $forbidden = ['sylius plus', 'enterprise', 'moins cher', 'cheaper', 'meilleur que', 'better than', 'leader', '€', 'tarif', 'pricing', 'garanti', 'guaranteed'];
        foreach (['fr', 'en'] as $language) {
            foreach ($this->texts($language) as $key => $text) {
                foreach ($forbidden as $term) {
                    self::assertStringNotContainsString($term, mb_strtolower($text), $language . ': ' . $key);
                }
            }
        }
    }

    /** "B2B" and "B2C" are words, not figures. */
    private static function withoutTradeTerms(string $text): string
    {
        return (string) preg_replace('/\bB2[BC]\b/', '', $text);
    }

    /** @return array<string, string> */
    private function texts(string $language): array
    {
        $file = \dirname(__DIR__, 3) . '/translations/architecture.' . $language . '.yaml';
        self::assertFileExists($file);
        $tree = Yaml::parseFile($file);
        self::assertIsArray($tree);

        return $this->flatten($tree);
    }

    /**
     * @param array<array-key, mixed> $tree
     *
     * @return array<string, string>
     */
    private function flatten(array $tree, string $prefix = ''): array
    {
        $flat = [];
        foreach ($tree as $key => $value) {
            $path = '' === $prefix ? (string) $key : $prefix . '.' . $key;
            if (\is_array($value)) {
                $flat += $this->flatten($value, $path);
            } elseif (\is_string($value)) {
                $flat[$path] = $value;
            }
        }

        return $flat;
    }

    /** @param array<string, mixed> $proof */
    private function file(array $proof): string
    {
        $path = sys_get_temp_dir() . '/proof-architecture-' . bin2hex(random_bytes(4)) . '.json';
        file_put_contents($path, json_encode($proof, \JSON_THROW_ON_ERROR));
        $this->files[] = $path;

        return $path;
    }

    private function page(string $path): Crawler
    {
        self::ensureKernelShutdown();

        return self::createClient()->request('GET', $path);
    }
}
