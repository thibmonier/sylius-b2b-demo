<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Tests\Support\DemoCatalogs;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-205: the "Technical proof" page continues the editorial home (validated mock-up, decision of 2026-10-28): six
 * summary tiles counted from the report, the date of the snapshot shown once (a value keeps its own date only when it
 * differs), the coverage column hidden while no plugin has a figure, a card per plugin on a phone.
 */
final class ProofPageRedesignTest extends WebTestCase
{
    use EnvironmentSwitch;

    private const QUICK = 'sylius-quick-order-plugin';

    private const PACKAGIST = 'https://packagist.org/packages/tmonier/sylius-quick-order-plugin';

    private const RUN = 'https://github.com/thibmonier/sylius-quick-order-plugin/actions/runs/39117178731';

    private const TILES = ['versions', 'sylius', 'ci', 'phpstan', 'tests', 'coverage'];

    /** @var list<string> */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }
        parent::tearDown();
    }

    /** @return iterable<string, array{string, string, string, string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR', 'Fichiers de test des plugins', 'Non mesurée', 'non disponible'];
        yield 'English' => ['en_US', 'Test files in the plugins', 'Not measured', 'not available'];
    }

    #[DataProvider('locales')]
    public function testThePageUsesTheClassesOfTheHomeAndHasNoInlineStyle(string $locale): void
    {
        $page = $this->open($locale, $this->proof());

        self::assertResponseIsSuccessful();
        self::assertCount(1, $page->filter('h1.b2b-home-h1'));
        self::assertGreaterThan(0, $page->filter('.b2b-home-eyebrow')->count());
        self::assertGreaterThan(0, $page->filter('.b2b-home-wrap')->count());
        self::assertGreaterThanOrEqual(3, $page->filter('section.b2b-home-section')->count());
        self::assertCount(0, $page->filter('[data-page="proof"] style'), 'the styles live in the partial _proof.scss');
    }

    #[DataProvider('locales')]
    public function testTheSectionsAreReachableFromThreeAnchors(string $locale): void
    {
        $page = $this->open($locale, $this->proof());

        $anchors = $page->filter('nav[data-proof-sections] a')->each(static fn (Crawler $a): string => (string) $a->attr('href'));
        self::assertSame(['#synthese', '#livraison', '#plugins'], $anchors);
        foreach ($anchors as $anchor) {
            self::assertCount(1, $page->filter('section' . $anchor), $anchor);
        }
    }

    #[DataProvider('locales')]
    public function testSixTilesAreCountedFromTheReportWithoutAnyFigureWrittenByHand(string $locale, string $testsLabel, string $unmeasured): void
    {
        $page = $this->open($locale, $this->proof());
        $total = DemoCatalogs::pluginCount();

        self::assertCount(6, $page->filter('[data-proof-summary] [data-proof-tile]'));
        self::assertSame(self::TILES, $page->filter('[data-proof-tile]')->each(static fn (Crawler $tile): string => (string) $tile->attr('data-proof-tile')));
        foreach (['versions', 'sylius', 'ci', 'phpstan'] as $tile) {
            self::assertSame('1/' . $total, $this->compact($page->filter('[data-proof-tile="' . $tile . '"] [data-proof-value]')->text()), $tile);
        }
        self::assertSame('21', $this->compact($page->filter('[data-proof-tile="tests"] [data-proof-value]')->text()));
        self::assertStringContainsString($unmeasured, $page->filter('[data-proof-tile="coverage"] [data-proof-value]')->text());
    }

    #[DataProvider('locales')]
    public function testTheTestFilesTileSaysWhatItCountsAndGivesTheFilesOfTheDemoApart(string $locale, string $testsLabel): void
    {
        $tile = $this->open($locale, $this->proof())->filter('[data-proof-tile="tests"]');

        self::assertStringContainsString($testsLabel, $tile->text());
        self::assertStringContainsString('161', $tile->filter('[data-proof-note]')->text());
        self::assertStringNotContainsString('182', $tile->text(), 'the plugin files and the files of the demo are never added');
    }

    public function testTheTestFilesTileDoesNotMentionTheDemoWhenItsFigureIsMissing(): void
    {
        $proof = $this->proof();
        unset($proof['delivery']['tests']);

        $note = $this->open('fr_FR', $proof)->filter('[data-proof-tile="tests"] [data-proof-note]')->text();

        self::assertStringNotContainsString('démo elle-même', $note);
    }

    #[DataProvider('locales')]
    public function testTheDateOfTheSnapshotIsShownOnceAndAValueKeepsItsDateOnlyWhenItDiffers(string $locale): void
    {
        $page = $this->open($locale, $this->proof());

        self::assertSame('2026-10-21', $page->filter('[data-proof-generated] time')->attr('datetime'));
        $row = $page->filter('tr[data-plugin="' . self::QUICK . '"]');
        self::assertCount(0, $row->filter('[data-proof-cell="version"] time'), 'same date as the snapshot: no date next to the value');
        self::assertCount(0, $row->filter('[data-proof-cell="tests"] time'));
        self::assertSame('2026-10-14', $row->filter('[data-proof-cell="ci"] time')->attr('datetime'), 'a different date stays next to its value');
        self::assertSame(1, $page->filter('[data-proof-table] time')->count() + $page->filter('[data-proof-delivery] time')->count());
    }

    #[DataProvider('locales')]
    public function testTheCoverageColumnIsHiddenWhileNoPluginHasAFigure(string $locale): void
    {
        $page = $this->open($locale, $this->proof());

        self::assertCount(0, $page->filter('[data-proof-cell="coverage"]'));
        self::assertCount(6, $page->filter('table[data-proof-table] thead th'), 'plugin, version, Sylius, CI, PHPStan and test files');
    }

    public function testTheCoverageColumnAppearsWithTheFirstFigure(): void
    {
        $proof = $this->proof();
        $proof['plugins'][self::QUICK]['coverage'] = ['value' => 87, 'source' => 'https://github.com/thibmonier/sylius-quick-order-plugin/blob/main/coverage.xml', 'checked_at' => '2026-10-21'];

        $page = $this->open('fr_FR', $proof);

        self::assertCount(DemoCatalogs::pluginCount(), $page->filter('tr[data-plugin] [data-proof-cell="coverage"]'));
        self::assertCount(7, $page->filter('table[data-proof-table] thead th'));
        self::assertStringContainsString('87', $page->filter('tr[data-plugin="' . self::QUICK . '"] [data-proof-cell="coverage"]')->text());
        self::assertSame('1/' . DemoCatalogs::pluginCount(), $this->compact($page->filter('[data-proof-tile="coverage"] [data-proof-value]')->text()));
    }

    #[DataProvider('locales')]
    public function testEveryPluginHasACardForPhonesWithTheSameSourcesAsTheTable(string $locale): void
    {
        $page = $this->open($locale, $this->proof());

        self::assertCount(DemoCatalogs::pluginCount(), $page->filter('ul[data-proof-cards] > li[data-proof-card]'));
        $card = $page->filter('li[data-proof-card="' . self::QUICK . '"]');
        self::assertSame(self::PACKAGIST, $card->filter('[data-proof-card-field="version"] a')->attr('href'));
        self::assertSame(self::RUN, $card->filter('[data-proof-card-field="ci"] a')->attr('href'));
        self::assertStringContainsString('2.2, 2.3', $card->filter('[data-proof-card-field="sylius"]')->text());
        self::assertStringContainsString('21', $card->filter('[data-proof-card-field="tests"]')->text());
        self::assertSame(1, $card->filter('h3')->count());
    }

    public function testTheTableAndTheCardsShowOnTheirOwnWidthWithoutTheB2bStylesheet(): void
    {
        $page = $this->open('fr_FR', $this->proof());

        self::assertStringContainsString('d-none', (string) $page->filter('[data-proof-table-wrap]')->attr('class'));
        self::assertStringContainsString('d-lg-block', (string) $page->filter('[data-proof-table-wrap]')->attr('class'));
        self::assertStringContainsString('d-lg-none', (string) $page->filter('ul[data-proof-cards]')->attr('class'));
    }

    #[DataProvider('locales')]
    public function testAStateIsAlwaysSaidInWordsAndNeverByColourAlone(string $locale, string $label, string $unmeasured, string $unavailable): void
    {
        $page = $this->open($locale, $this->proof());

        $ci = $page->filter('tr[data-plugin="' . self::QUICK . '"] [data-proof-cell="ci"] a');
        self::assertSame('fr_FR' === $locale ? 'réussie' : 'passed', mb_strtolower(trim($ci->filter('[data-proof-label]')->text())));
        self::assertSame('true', $ci->filter('[aria-hidden]')->attr('aria-hidden'), 'the tick is decoration');
        $missing = $page->filter('tr[data-plugin="sylius-agentic-feeds-plugin"] [data-proof-cell="version"]');
        self::assertStringContainsString($unavailable, $missing->text());
        self::assertCount(0, $missing->filter('a'));
    }

    #[DataProvider('locales')]
    public function testEveryExternalLinkOpensANewTabAndSaysSo(string $locale): void
    {
        $page = $this->open($locale, $this->proof());

        $links = $page->filter('[data-page="proof"] a[href^="https://"]');
        self::assertGreaterThan(0, $links->count());
        foreach ($links as $link) {
            $anchor = new Crawler($link);
            self::assertSame('_blank', $anchor->attr('target'), (string) $anchor->attr('href'));
            self::assertStringContainsString('noopener', (string) $anchor->attr('rel'));
            self::assertCount(1, $anchor->filter('.visually-hidden'), (string) $anchor->attr('href'));
        }
    }

    #[DataProvider('locales')]
    public function testTheErpPluginStaysInTheTableAndTheCards(string $locale): void
    {
        $page = $this->open($locale, $this->proof());

        self::assertCount(1, $page->filter('tr[data-plugin="sylius-erp-flatfile-plugin"]'));
        self::assertCount(1, $page->filter('li[data-proof-card="sylius-erp-flatfile-plugin"]'));
    }

    #[DataProvider('locales')]
    public function testNoTextIsLeftUntranslatedAndTheToneStaysNeutral(string $locale): void
    {
        $page = $this->open($locale, $this->proof());
        $text = $page->filter('[data-page="proof"]')->text();

        self::assertSame(0, preg_match('/\bproof\.[a-z_]+\.[a-z_]+/', $text), 'a translation key is shown as is');
        self::assertSame(0, preg_match('/sylius\s+plus|concurren|competitor|alternative|gratuit/i', $text));
        if ('en_US' === $locale) {
            self::assertStringNotContainsString('Synthèse', $text);
            self::assertStringNotContainsString('Relevé', $text);
        }
    }

    /** @return string the text without spaces, so that "1 / 27" and "1/27" compare equal */
    private function compact(string $text): string
    {
        return (string) preg_replace('/\s+/u', '', $text);
    }

    /** @param array<string, mixed> $proof */
    private function open(string $locale, array $proof): Crawler
    {
        $this->setEnvironment(['DEMO_PROOF_FILE' => $this->file((string) json_encode($proof, \JSON_THROW_ON_ERROR))]);
        self::ensureKernelShutdown();

        return self::createClient()->request('GET', '/' . $locale . '/preuve');
    }

    private function file(string $content): string
    {
        $file = tempnam(sys_get_temp_dir(), 'proof-redesign') ?: throw new \RuntimeException('no temporary file');
        file_put_contents($file, $content);

        return $this->files[] = $file;
    }

    /** @return array{generated_at: string, plugins: array<string, array<string, array<string, mixed>>>, delivery: array<string, array<string, mixed>>} */
    private function proof(): array
    {
        $fact = static fn (string|int|bool $value, string $source, string $date = '2026-10-21'): array => ['value' => $value, 'source' => $source, 'checked_at' => $date];
        $delivery = 'https://github.com/thibmonier/sylius-b2b-demo/blob/main/.github/workflows/ci.yaml';

        return [
            'generated_at' => '2026-10-21T10:00:00Z',
            'plugins' => [
                self::QUICK => [
                    'version' => $fact('v1.2.0', self::PACKAGIST),
                    'sylius_declared' => $fact('^2.2.9 || ^2.3', self::PACKAGIST),
                    'sylius_23' => $fact(true, self::PACKAGIST),
                    'sylius_tested' => $fact('2.2, 2.3', 'https://github.com/thibmonier/sylius-quick-order-plugin/blob/main/.github/workflows/build.yaml'),
                    'ci' => $fact('success', self::RUN, '2026-10-14'),
                    'phpstan' => $fact('max', 'https://github.com/thibmonier/sylius-quick-order-plugin/blob/main/phpstan.neon'),
                    'tests' => $fact(21, 'https://github.com/thibmonier/sylius-quick-order-plugin/tree/main/tests'),
                ],
            ],
            'delivery' => [
                'tests' => $fact(161, 'https://github.com/thibmonier/sylius-b2b-demo/tree/main/tests'),
                'phpstan' => $fact('9', 'https://github.com/thibmonier/sylius-b2b-demo/blob/main/phpstan.dist.neon'),
                'ecs' => $fact('ECS', $delivery),
                'composer_audit' => $fact('composer audit', $delivery),
                'image_scan' => $fact('Trivy', $delivery),
                'sbom' => $fact('CycloneDX', $delivery),
                'ci' => $fact('success', 'https://github.com/thibmonier/sylius-b2b-demo/actions/runs/1'),
            ],
        ];
    }
}
