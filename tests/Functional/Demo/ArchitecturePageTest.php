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

    #[DataProvider('locales')]
    public function testTheDiagramHasATitleAndATextualEquivalentAndFollowsTheTheme(string $locale): void
    {
        $page = $this->page('/' . $locale . '/architecture');

        $diagram = $page->filter('svg[data-architecture-overview][role="img"]');
        self::assertCount(1, $diagram);
        foreach (explode(' ', (string) $diagram->attr('aria-labelledby')) as $id) {
            self::assertNotSame('', trim($page->filter('#' . $id)->text()), $id);
        }
        self::assertGreaterThan(120, mb_strlen($diagram->filter('desc')->text()), 'the description replaces the diagram for a screen reader');
        $markup = $diagram->outerHtml();
        self::assertStringContainsString('currentColor', $markup);
        self::assertDoesNotMatchRegularExpression('/(fill|stroke)="#/', $markup, 'colours follow the light and dark themes');
        self::assertStringContainsString('w-100', (string) $diagram->attr('class'), 'scales down to a 360 px screen');
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

    public function testThePageStaysOutOfTheSearchEnginesAndHasItsOwnDescription(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $page = $client->request('GET', '/fr_FR/architecture');

        self::assertSame('noindex, nofollow', $client->getResponse()->headers->get('X-Robots-Tag'));
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
