<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Tests\Support\DataSets;
use App\Tests\Support\DemoCatalogs;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-111: the technical proof page. One row per plugin of the catalogue; every figure is a link to its public source with
 * the date it was checked; what cannot be verified is not displayed ("not published", "not measured", "not available");
 * a missing or broken data file gives a normal page, never an error 500.
 */
final class ProofPageTest extends WebTestCase
{
    use EnvironmentSwitch;

    private const QUICK = 'sylius-quick-order-plugin';

    private const PACKAGIST = 'https://packagist.org/packages/tmonier/sylius-quick-order-plugin';

    private const RUN = 'https://github.com/thibmonier/sylius-quick-order-plugin/actions/runs/39117178731';

    private const WORKFLOW = 'https://github.com/thibmonier/sylius-quick-order-plugin/blob/main/.github/workflows/build.yaml';

    private const DEMO_CI = 'https://github.com/thibmonier/sylius-b2b-demo/blob/main/.github/workflows/ci.yaml';

    /** @var list<string> */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }
        parent::tearDown();
    }

    /** @return iterable<string, array{string, string, string, string, string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR', 'non publié', 'non mesurée', 'non disponible', 'Comment cette démo est livrée'];
        yield 'English' => ['en_US', 'not published', 'not measured', 'not available', 'How this demo is delivered'];
    }

    #[DataProvider('locales')]
    public function testEveryPluginHasARowAndEveryVerifiedFigureLinksToItsSourceWithItsDate(string $locale, string $unpublished, string $unmeasured, string $unavailable, string $deliveryTitle): void
    {
        $page = $this->open($locale, $this->proof());

        self::assertResponseIsSuccessful();
        self::assertCount(1, $page->filter('h1'));
        self::assertCount(DemoCatalogs::pluginCount(), $page->filter('table[data-proof-table] tbody tr[data-plugin]'));
        $row = $page->filter('tr[data-plugin="' . self::QUICK . '"]');
        self::assertSame(self::PACKAGIST, $row->filter('[data-proof-cell="version"] a')->attr('href'));
        self::assertStringContainsString('v1.2.0', $row->filter('[data-proof-cell="version"]')->text());
        // US-205 (decision of 2026-10-28): a value has the date of the snapshot, so it carries no date of its own.
        self::assertCount(0, $row->filter('[data-proof-cell="version"] time'));
        self::assertSame('2026-10-21', $page->filter('[data-proof-generated] time')->attr('datetime'));
        self::assertStringContainsString('2.3', $row->filter('[data-proof-cell="sylius"]')->text());
        self::assertStringContainsString('2.2, 2.3', $row->filter('[data-proof-cell="sylius"]')->text());
        self::assertSame(self::RUN, $row->filter('[data-proof-cell="ci"] a')->attr('href'));
        self::assertStringContainsString('max', $row->filter('[data-proof-cell="phpstan"]')->text());
        self::assertStringContainsString('21', $row->filter('[data-proof-cell="tests"]')->text());
        // US-205 (decision of 2026-10-28): no plugin has a coverage figure, so the column is not displayed.
        self::assertCount(0, $page->filter('[data-proof-cell="coverage"]'));
        self::assertStringContainsString($deliveryTitle, $page->filter('[data-proof-delivery] h2')->text());
    }

    #[DataProvider('localesWith3Arguments')]
    public function testAnUnpublishedPluginSaysSoAndACoverageIsNeverAFigure(string $locale, string $unpublished, string $unmeasured): void
    {
        $page = $this->open($locale, $this->proof());

        $row = $page->filter('tr[data-plugin="sylius-customer-asset-management-plugin"]');
        self::assertStringContainsString($unpublished, $row->filter('[data-proof-cell="version"]')->text());
        // US-205 (decision of 2026-10-28): the column is hidden while no plugin has a figure; the summary says "not measured".
        self::assertCount(0, $page->filter('[data-proof-cell="coverage"]'));
        self::assertStringContainsString($unmeasured, mb_strtolower($page->filter('[data-proof-tile="coverage"]')->text()));
        self::assertDoesNotMatchRegularExpression('/\d\s*%/', $page->filter('[data-proof-table]')->text());
    }

    #[DataProvider('localesWith4Arguments')]
    public function testAFigureWithoutSourceIsNotDisplayed(string $locale, string $unpublished, string $unmeasured, string $unavailable): void
    {
        $proof = $this->proof();
        unset($proof['plugins'][self::QUICK]['tests']['source']);
        $proof['plugins'][self::QUICK]['phpstan']['source'] = 'http://example.com/not-https';

        $page = $this->open($locale, $proof);

        $row = $page->filter('tr[data-plugin="' . self::QUICK . '"]');
        foreach (['tests', 'phpstan'] as $cell) {
            self::assertStringContainsString($unavailable, $row->filter('[data-proof-cell="' . $cell . '"]')->text());
            self::assertCount(0, $row->filter('[data-proof-cell="' . $cell . '"] a'));
        }
        self::assertStringNotContainsString('21', $row->filter('[data-proof-cell="tests"]')->text());
    }

    #[DataProvider('localesWith1Argument')]
    public function testALastValueKeptAfterAFailedRefreshIsServedWithItsDate(string $locale): void
    {
        $proof = $this->proof();
        $proof['plugins'][self::QUICK]['ci'] = ['value' => 'success', 'source' => self::RUN, 'checked_at' => '2026-10-14', 'stale' => true];

        $page = $this->open($locale, $proof);

        $cell = $page->filter('tr[data-plugin="' . self::QUICK . '"] [data-proof-cell="ci"]');
        self::assertSame('2026-10-14', $cell->filter('time')->attr('datetime'));
        self::assertCount(1, $cell->filter('[data-proof-stale]'));
    }

    #[DataProvider('localesWith4Arguments')]
    public function testAMissingOrBrokenFileGivesANormalPageWithNothingAvailable(string $locale, string $unpublished, string $unmeasured, string $unavailable): void
    {
        foreach ([null, '{ not json'] as $content) {
            $this->setEnvironment(['DEMO_PROOF_FILE' => null === $content ? sys_get_temp_dir() . '/proof-missing-' . bin2hex(random_bytes(4)) . '.json' : $this->file($content)]);
            self::ensureKernelShutdown();
            $client = self::createClient();

            $page = $client->request('GET', '/' . $locale . '/preuve');

            self::assertResponseIsSuccessful();
            self::assertCount(DemoCatalogs::pluginCount(), $page->filter('tr[data-plugin]'));
            self::assertStringContainsString($unavailable, $page->filter('tr[data-plugin="' . self::QUICK . '"] [data-proof-cell="version"]')->text());
            self::assertCount(0, $page->filter('table[data-proof-table] a[href^="https://"]'));
        }
    }

    #[DataProvider('localesWith4Arguments')]
    public function testTheDeliveryLineStatesWhatTheCiOfTheDemoDoes(string $locale, string $unpublished, string $unmeasured, string $unavailable): void
    {
        $proof = $this->proof();
        unset($proof['delivery']['sbom']);

        $page = $this->open($locale, $proof);

        $delivery = $page->filter('[data-proof-delivery]');
        self::assertStringContainsString('161', $delivery->filter('[data-proof-delivery-item="tests"]')->text());
        self::assertStringContainsString('PHPStan', $delivery->filter('[data-proof-delivery-item="phpstan"]')->text());
        self::assertStringContainsString('Trivy', $delivery->filter('[data-proof-delivery-item="image_scan"]')->text());
        self::assertSame(self::DEMO_CI, $delivery->filter('[data-proof-delivery-item="image_scan"] a')->attr('href'));
        self::assertStringContainsString($unavailable, $delivery->filter('[data-proof-delivery-item="sbom"]')->text());
    }

    public function testTheTwoLanguagesShowTheSameRowsAndTheSameSources(): void
    {
        $french = $this->describe($this->open('fr_FR', $this->proof()));
        $english = $this->describe($this->open('en_US', $this->proof()));

        self::assertSame($french, $english);
        self::assertNotSame([], $french['links']);
    }

    public function testThePluginsPageAndTheFooterLeadToTheProof(): void
    {
        $client = self::createClient();

        $plugins = $client->request('GET', '/fr_FR/plugins');
        self::assertSame('/fr_FR/preuve', $plugins->filter('a[data-plugins-proof-link]')->attr('href'));
        self::assertCount(1, $plugins->filter('footer [data-demo-footer] a[href="/fr_FR/preuve"]'));
    }

    public function testThePageHasItsOwnDescription(): void
    {
        $this->setEnvironment(['DEMO_PROOF_FILE' => $this->file((string) json_encode($this->proof()))]);
        $client = self::createClient();

        $french = $client->request('GET', '/fr_FR/preuve')->filter('meta[name="description"]')->attr('content');
        $client->request('GET', '/fr_FR/plugins');
        $plugins = $client->getCrawler()->filter('meta[name="description"]')->attr('content');

        self::assertNotSame($plugins, $french);
        self::assertStringContainsString('Packagist', (string) $french);
    }

    public function testThePageMakesNoOutboundRequest(): void
    {
        $source = (string) file_get_contents(\dirname(__DIR__, 3) . '/src/Demo/Proof/ProofReader.php') . (string) file_get_contents(\dirname(__DIR__, 3) . '/src/Demo/Controller/ProofController.php');

        foreach (['HttpClient', 'curl_', 'file_get_contents(\'http', 'Process'] as $call) {
            self::assertStringNotContainsString($call, $source);
        }
    }

    /** @return array{links: list<string>, rows: list<string>} */
    private function describe(Crawler $page): array
    {
        return [
            'rows' => $page->filter('tr[data-plugin]')->each(static fn (Crawler $row): string => (string) $row->attr('data-plugin')),
            // Internal links carry the locale of the page: only their target is compared.
            'links' => $page->filter('table[data-proof-table] a, [data-proof-delivery] a')->each(static fn (Crawler $a): string => (string) preg_replace('#^/(fr_FR|en_US)/#', '/{locale}/', (string) $a->attr('href'))),
        ];
    }

    /** @param array{generated_at: string, plugins: array<string, array<string, array<string, mixed>>>, delivery: array<string, array<string, mixed>>} $proof */
    private function open(string $locale, array $proof): Crawler
    {
        $this->setEnvironment(['DEMO_PROOF_FILE' => $this->file((string) json_encode($proof, \JSON_THROW_ON_ERROR))]);

        self::ensureKernelShutdown();

        return self::createClient()->request('GET', '/' . $locale . '/preuve');
    }

    private function file(string $content): string
    {
        $file = tempnam(sys_get_temp_dir(), 'proof-page') ?: throw new \RuntimeException('no temporary file');
        file_put_contents($file, $content);

        return $this->files[] = $file;
    }

    /** @return array{generated_at: string, plugins: array<string, array<string, array<string, mixed>>>, delivery: array<string, array<string, mixed>>} */
    private function proof(): array
    {
        $fact = static fn (string|int|bool $value, string $source, string $date = '2026-10-21'): array => ['value' => $value, 'source' => $source, 'checked_at' => $date];

        return [
            'generated_at' => '2026-10-21T10:00:00Z',
            'plugins' => [
                self::QUICK => [
                    'version' => $fact('v1.2.0', self::PACKAGIST),
                    'sylius_declared' => $fact('^2.2.9 || ^2.3', self::PACKAGIST),
                    'sylius_23' => $fact(true, self::PACKAGIST),
                    'sylius_tested' => $fact('2.2, 2.3', self::WORKFLOW),
                    'ci' => $fact('success', self::RUN),
                    'phpstan' => $fact('max', 'https://github.com/thibmonier/sylius-quick-order-plugin/blob/main/phpstan.neon'),
                    'tests' => $fact(21, 'https://github.com/thibmonier/sylius-quick-order-plugin/tree/main/tests'),
                ],
                'sylius-customer-asset-management-plugin' => [
                    'version' => ['state' => 'unpublished', 'source' => 'https://packagist.org/packages/tmonier/sylius-customer-asset-management-plugin', 'checked_at' => '2026-10-21'],
                ],
            ],
            'delivery' => [
                'tests' => $fact(161, 'https://github.com/thibmonier/sylius-b2b-demo/tree/main/tests'),
                'phpstan' => $fact('9', 'https://github.com/thibmonier/sylius-b2b-demo/blob/main/phpstan.dist.neon'),
                'ecs' => $fact('ECS', self::DEMO_CI),
                'composer_audit' => $fact('composer audit', self::DEMO_CI),
                'image_scan' => $fact('Trivy', self::DEMO_CI),
                'sbom' => $fact('CycloneDX', self::DEMO_CI),
                'ci' => $fact('success', 'https://github.com/thibmonier/sylius-b2b-demo/actions/runs/1'),
            ],
        ];
    }

    /** @return iterable<array-key, list<mixed>> */
    public static function localesWith1Argument(): iterable
    {
        return DataSets::firstArguments(self::locales(), 1);
    }

    /** @return iterable<array-key, list<mixed>> */
    public static function localesWith3Arguments(): iterable
    {
        return DataSets::firstArguments(self::locales(), 3);
    }

    /** @return iterable<array-key, list<mixed>> */
    public static function localesWith4Arguments(): iterable
    {
        return DataSets::firstArguments(self::locales(), 4);
    }
}
