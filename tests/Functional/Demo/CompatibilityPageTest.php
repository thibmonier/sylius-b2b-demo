<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-171: the compatibility matrix and the dependency graph. One row per plugin of the catalogue; the versions tested by
 * the plugin's own CI and the combination of the demo are two different columns; a mandatory link and a suggestion differ
 * by more than colour; the page reads a versioned file and shows no figure of its own; a missing file gives a normal page.
 */
final class CompatibilityPageTest extends WebTestCase
{
    use EnvironmentSwitch;

    private const TRADE = 'sylius-trade-credit-plugin';

    private const HOLD = 'sylius-order-hold-plugin';

    private const FACTURX = 'sylius-facturx-plugin';

    private const LOCK = 'https://github.com/thibmonier/sylius-b2b-demo/blob/main/composer.lock';

    /** @var list<string> */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }
        parent::tearDown();
    }

    /** @return iterable<string, array{string, string, string, string, string, string, string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR', 'Testé par la CI du plugin', 'Testé ensemble dans la démo', 'obligatoire', 'optionnel', 'non disponible', 'non installé dans la démo'];
        yield 'English' => ['en_US', "Tested by the plugin's CI", 'Tested together in the demo', 'required', 'optional', 'not available', 'not installed in the demo'];
    }

    #[DataProvider('locales')]
    public function testEveryPluginHasARowWithTheTwoDistinctColumns(string $locale, string $ciColumn, string $demoColumn): void
    {
        $page = $this->open($locale, $this->data());

        self::assertResponseIsSuccessful();
        self::assertCount(1, $page->filter('h1'));
        self::assertCount(25, $page->filter('table[data-compat-table] tbody tr[data-plugin]'));
        $headers = $page->filter('table[data-compat-table] thead th')->each(static fn (Crawler $th): string => trim($th->text()));
        self::assertContains($ciColumn, $headers);
        self::assertContains($demoColumn, $headers);
        $row = $page->filter('tr[data-plugin="' . self::TRADE . '"]');
        foreach (['php' => '8.3, 8.4', 'sylius' => '2.2, 2.3', 'symfony' => '7.4, 8.0', 'database' => 'mysql:8.4'] as $axis => $values) {
            self::assertStringContainsString($values, $row->filter('[data-compat-cell="ci"] [data-compat-axis="' . $axis . '"]')->text());
        }
        self::assertSame('https://github.com/thibmonier/sylius-trade-credit-plugin/blob/main/.github/workflows/build.yaml', $row->filter('[data-compat-cell="ci"] a')->attr('href'));
        self::assertSame('2026-10-23', $row->filter('[data-compat-cell="ci"] time')->attr('datetime'));
    }

    #[DataProvider('locales')]
    public function testTheDemoColumnStatesTheCombinationOfTheDemoAndNothingMore(string $locale, string $ciColumn, string $demoColumn, string $required, string $optional, string $unavailable, string $notInstalled): void
    {
        $page = $this->open($locale, $this->data());

        $installed = $page->filter('tr[data-plugin="' . self::TRADE . '"] [data-compat-cell="demo"]');
        self::assertStringContainsString('v1.1.0', $installed->text());
        self::assertStringContainsString('2.3.2', $installed->text());
        self::assertStringContainsString('8.1.9', $installed->text());
        self::assertSame(self::LOCK, $installed->filter('a')->attr('href'));
        $other = $page->filter('tr[data-plugin="' . self::HOLD . '"] [data-compat-cell="demo"]');
        self::assertStringContainsString($notInstalled, $other->text());
        self::assertStringNotContainsString('2.3.2', $other->text());
    }

    #[DataProvider('locales')]
    public function testTradeCreditRequiresOrderHoldAndOnlyOptionallyLinksFacturx(string $locale, string $ciColumn, string $demoColumn, string $required, string $optional): void
    {
        $page = $this->open($locale, $this->data());

        $row = $page->filter('tr[data-plugin="' . self::TRADE . '"]');
        $requires = $row->filter('[data-compat-cell="requires"]');
        self::assertCount(1, $requires->filter('[data-compat-link="' . self::HOLD . '"]'));
        self::assertCount(0, $requires->filter('[data-compat-link="' . self::FACTURX . '"]'));
        self::assertStringContainsString($required, $requires->text());
        $suggests = $row->filter('[data-compat-cell="suggests"]');
        self::assertCount(1, $suggests->filter('[data-compat-link="' . self::FACTURX . '"]'));
        self::assertStringContainsString($optional, $suggests->text());
    }

    #[DataProvider('locales')]
    public function testTheGraphTellsAMandatoryLinkFromAnOptionalOneByMoreThanColour(string $locale, string $ciColumn, string $demoColumn, string $required, string $optional): void
    {
        $page = $this->open($locale, $this->data());

        $graph = $page->filter('svg[data-compat-graph]');
        self::assertCount(1, $graph);
        self::assertSame('img', $graph->attr('role'));
        self::assertNotSame('', trim($graph->filter('title')->text()));
        self::assertNotSame('', trim($graph->filter('desc')->text()));
        $mandatory = $graph->filter('[data-edge-from="' . self::TRADE . '"][data-edge-to="' . self::HOLD . '"]');
        $suggested = $graph->filter('[data-edge-from="' . self::TRADE . '"][data-edge-to="' . self::FACTURX . '"]');
        self::assertSame('required', $mandatory->attr('data-edge-kind'));
        self::assertSame('optional', $suggested->attr('data-edge-kind'));
        self::assertNull($mandatory->attr('stroke-dasharray'), 'solid line');
        self::assertNotSame('', (string) $suggested->attr('stroke-dasharray'), 'dashed line');
        $legend = $page->filter('[data-compat-legend]')->text();
        self::assertStringContainsString($required, $legend);
        self::assertStringContainsString($optional, $legend);
        // The same links in text, for who cannot read the drawing.
        self::assertCount(1, $page->filter('[data-compat-links] [data-compat-edge-from="' . self::TRADE . '"][data-compat-edge-to="' . self::FACTURX . '"]'));
    }

    #[DataProvider('locales')]
    public function testThePageNeverSaysTheWholeCatalogueWasValidatedTogether(string $locale): void
    {
        $page = $this->open($locale, $this->data());

        $text = $page->filter('[data-page="compatibility"]')->text();
        self::assertDoesNotMatchRegularExpression('/valid(é|és|ated)[^.]*(ensemble|together)/iu', $text);
        self::assertDoesNotMatchRegularExpression('/\bPlus\b/', $text, 'no comparison with a commercial offer');
        self::assertCount(1, $page->filter('[data-compat-scope]'));
    }

    #[DataProvider('locales')]
    public function testALastValueKeptAfterAFailedGenerationIsServedWithItsDate(string $locale): void
    {
        $data = $this->data();
        $data['plugins'][self::TRADE]['ci']['stale'] = true;
        $data['plugins'][self::TRADE]['ci']['checked_at'] = '2026-10-14';

        $page = $this->open($locale, $data);

        $cell = $page->filter('tr[data-plugin="' . self::TRADE . '"] [data-compat-cell="ci"]');
        self::assertSame('2026-10-14', $cell->filter('time')->attr('datetime'));
        self::assertCount(1, $cell->filter('[data-compat-stale]'));
    }

    #[DataProvider('locales')]
    public function testAMissingOrBrokenFileGivesANormalPageWithNothingAvailable(string $locale, string $ciColumn, string $demoColumn, string $required, string $optional, string $unavailable): void
    {
        foreach ([null, '{ not json'] as $content) {
            $this->setEnvironment(['DEMO_COMPATIBILITY_FILE' => null === $content ? sys_get_temp_dir() . '/compat-missing-' . bin2hex(random_bytes(4)) . '.json' : $this->file($content)]);
            self::ensureKernelShutdown();

            $page = self::createClient()->request('GET', '/' . $locale . '/compatibilite');

            self::assertResponseIsSuccessful();
            self::assertCount(25, $page->filter('tr[data-plugin]'));
            self::assertStringContainsString($unavailable, $page->filter('tr[data-plugin="' . self::TRADE . '"] [data-compat-cell="ci"]')->text());
            self::assertCount(0, $page->filter('table[data-compat-table] a[href^="https://"]'));
            self::assertCount(0, $page->filter('svg[data-compat-graph]'));
        }
    }

    public function testTheTwoLanguagesShowTheSameRowsEdgesAndSources(): void
    {
        $french = $this->describe($this->open('fr_FR', $this->data()));
        $english = $this->describe($this->open('en_US', $this->data()));

        self::assertSame($french, $english);
        self::assertNotSame([], $french['links']);
        self::assertNotSame([], $french['edges']);
    }

    public function testThePluginsPageLeadsToTheMatrix(): void
    {
        $plugins = self::createClient()->request('GET', '/fr_FR/plugins');

        self::assertSame('/fr_FR/compatibilite', $plugins->filter('a[data-plugins-compat-link]')->attr('href'));
    }

    public function testThePageHasItsOwnDescription(): void
    {
        $client = self::createClient();

        $matrix = $client->request('GET', '/fr_FR/compatibilite')->filter('meta[name="description"]')->attr('content');
        $plugins = $client->request('GET', '/fr_FR/plugins')->filter('meta[name="description"]')->attr('content');

        self::assertNotSame($plugins, $matrix);
        self::assertStringContainsString('dépendances', (string) $matrix);
    }

    public function testNoFigureIsWrittenInTheTemplateOrTheLabels(): void
    {
        $root = \dirname(__DIR__, 3);

        foreach (['/templates/demo/compatibility.html.twig', '/translations/compatibility.fr.yaml', '/translations/compatibility.en.yaml'] as $file) {
            $content = (string) file_get_contents($root . $file);
            self::assertNotSame('', $content, $file);
            self::assertDoesNotMatchRegularExpression('/\d+\.\d+/', $content, $file . ' states a version by hand');
            self::assertDoesNotMatchRegularExpression('/\b(Sylius|Symfony|PHP) \d/', $content, $file);
        }
    }

    public function testThePageMakesNoOutboundRequest(): void
    {
        $root = \dirname(__DIR__, 3);
        $source = (string) file_get_contents($root . '/src/Demo/Proof/Compatibility/CompatibilityReader.php')
            . (string) file_get_contents($root . '/src/Demo/Proof/Compatibility/DependencyGraph.php')
            . (string) file_get_contents($root . '/src/Demo/Controller/CompatibilityController.php');

        foreach (['HttpClient', 'curl_', 'file_get_contents(\'http', 'Process'] as $call) {
            self::assertStringNotContainsString($call, $source);
        }
    }

    /** @return array{rows: list<string>, links: list<string>, edges: list<string>} */
    private function describe(Crawler $page): array
    {
        return [
            'rows' => $page->filter('tr[data-plugin]')->each(static fn (Crawler $row): string => (string) $row->attr('data-plugin')),
            'links' => $page->filter('table[data-compat-table] a')->each(static fn (Crawler $a): string => (string) preg_replace('#^/(fr_FR|en_US)/#', '/{locale}/', (string) $a->attr('href'))),
            'edges' => $page->filter('svg[data-compat-graph] [data-edge-from]')->each(static fn (Crawler $edge): string => $edge->attr('data-edge-from') . '>' . $edge->attr('data-edge-to') . ':' . $edge->attr('data-edge-kind')),
        ];
    }

    /** @param array<string, mixed> $data */
    private function open(string $locale, array $data): Crawler
    {
        $this->setEnvironment(['DEMO_COMPATIBILITY_FILE' => $this->file((string) json_encode($data, \JSON_THROW_ON_ERROR))]);

        self::ensureKernelShutdown();

        return self::createClient()->request('GET', '/' . $locale . '/compatibilite');
    }

    private function file(string $content): string
    {
        $file = tempnam(sys_get_temp_dir(), 'compat-page') ?: throw new \RuntimeException('no temporary file');
        file_put_contents($file, $content);

        return $this->files[] = $file;
    }

    /** @return array<string, mixed> */
    private function data(): array
    {
        $workflow = static fn (string $slug): string => 'https://github.com/thibmonier/' . $slug . '/blob/main/.github/workflows/build.yaml';
        $composer = static fn (string $slug): string => 'https://github.com/thibmonier/' . $slug . '/blob/main/composer.json';
        $ci = static fn (string $slug, array $php): array => ['php' => $php, 'sylius' => ['2.2', '2.3'], 'symfony' => ['7.4', '8.0'], 'database' => ['mysql:8.4'], 'source' => $workflow($slug), 'checked_at' => '2026-10-23'];

        return [
            'generated_at' => '2026-10-23T10:00:00Z',
            'demo' => ['sylius' => '2.3.2', 'symfony' => '8.1.9', 'php' => '^8.5', 'source' => self::LOCK, 'checked_at' => '2026-10-23'],
            'plugins' => [
                self::TRADE => [
                    'dependencies' => ['requires' => [self::HOLD], 'suggests' => [self::FACTURX], 'source' => $composer(self::TRADE), 'checked_at' => '2026-10-23'],
                    'ci' => $ci(self::TRADE, ['8.3', '8.4']),
                    'demo' => ['version' => 'v1.1.0', 'source' => self::LOCK, 'checked_at' => '2026-10-23'],
                    'fingerprint' => 'a',
                ],
                self::HOLD => [
                    'dependencies' => ['requires' => [], 'suggests' => [], 'source' => $composer(self::HOLD), 'checked_at' => '2026-10-23'],
                    'ci' => $ci(self::HOLD, ['8.3', '8.4']),
                    'fingerprint' => 'b',
                ],
                self::FACTURX => [
                    'dependencies' => ['requires' => [], 'suggests' => [], 'source' => $composer(self::FACTURX), 'checked_at' => '2026-10-23'],
                    'ci' => $ci(self::FACTURX, ['8.3']),
                    'demo' => ['version' => 'v1.1.0', 'source' => self::LOCK, 'checked_at' => '2026-10-23'],
                    'fingerprint' => 'c',
                ],
            ],
        ];
    }
}
