<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Charter\PublicTextCharter;
use App\Tests\Support\DataSets;
use App\Tests\Support\Path;
use App\Tests\Unit\Demo\Packs\PackSamples;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-178: the page "Install a pack". One section per pack of the data file, with its plugins, its install command and the
 * steps left by hand; the list shown is the list of the file (itself read from the composer.json of the pack); a plugin that
 * is in no pack is sent to the one-by-one installation; the text carries the charter. The page reads a versioned file only.
 */
final class PacksPageTest extends WebTestCase
{
    use EnvironmentSwitch;

    /** Plugins kept out of the packs: the page never names one (ADR 0015). */
    private const KEPT_OUT = ['admin-order-creation', 'company-accounts', 'quick-order', 'quote-request', 'purchase-approval', 'tier-pricing', 'withdrawal', 'company-insights'];

    /** @var list<string> */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }
        parent::tearDown();
    }

    /** @return iterable<string, array{string, string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR', 'non disponible'];
        yield 'English' => ['en_US', 'not available'];
    }

    #[DataProvider('localesWith1Argument')]
    public function testEachPackShowsItsPluginsItsInstallCommandAndTheStepsLeftByHand(string $locale): void
    {
        $page = $this->open($locale, PackSamples::data());

        self::assertResponseIsSuccessful();
        self::assertCount(1, $page->filter('h1'));
        self::assertSame([PackSamples::REPOSITORY, PackSamples::OTHER_REPOSITORY], $page->filter('[data-pack]')->each(static fn (Crawler $pack): string => (string) $pack->attr('data-pack')));
        $pack = $page->filter('[data-pack="' . PackSamples::REPOSITORY . '"]');
        self::assertSame('composer require tmonier/sylius-pack-ai-headless', trim($pack->filter('[data-pack-command]')->text()));
        self::assertSame([PackSamples::FEEDS, PackSamples::OUTBOX], $pack->filter('[data-pack-plugin]')->each(static fn (Crawler $plugin): string => (string) $plugin->attr('data-pack-plugin')));
        self::assertStringContainsString('^1.0', $pack->filter('[data-pack-plugin="' . PackSamples::FEEDS . '"]')->text());
        $step = $pack->filter('tr[data-pack-step="' . PackSamples::FEEDS . '"]');
        self::assertStringContainsString('TmonierSyliusAgenticFeedsPlugin', $step->filter('[data-pack-cell="bundles"]')->text());
        self::assertStringContainsString('config/config.yaml', $step->filter('[data-pack-cell="config"]')->text());
        self::assertStringContainsString('config/routes.yaml', $step->filter('[data-pack-cell="routes"]')->text());
        self::assertStringContainsString('tmonier:feeds:generate', $step->filter('[data-pack-cell="other"]')->text());
        self::assertSame(['AI and headless', 'IA et headless'][(int) ('fr_FR' === $locale)], trim($pack->filter('h2')->first()->text()));
    }

    #[DataProvider('localesWith1Argument')]
    public function testTheRolesAndTheOtherStepsAreInTheLanguageOfThePage(string $locale): void
    {
        $page = $this->open($locale, PackSamples::data());

        $pack = $page->filter('[data-pack="' . PackSamples::REPOSITORY . '"]');
        $role = 'fr_FR' === $locale ? 'Flux produits pour les assistants IA' : 'Product feeds for AI assistants';
        $other = 'fr_FR' === $locale ? 'planifier' : 'schedule';
        self::assertStringContainsString($role, $pack->filter('[data-pack-plugin="' . PackSamples::FEEDS . '"]')->text());
        self::assertStringContainsString($other, $pack->filter('tr[data-pack-step="' . PackSamples::FEEDS . '"] [data-pack-cell="other"]')->text());
    }

    #[DataProvider('localesWith1Argument')]
    public function testTheTotalsAreComputedFromTheSteps(string $locale): void
    {
        $page = $this->open($locale, PackSamples::data());

        $totals = $page->filter('[data-pack="' . PackSamples::REPOSITORY . '"] [data-pack-totals]');
        self::assertSame('2', $totals->filter('[data-pack-total="bundles"]')->text());
        self::assertSame('2', $totals->filter('[data-pack-total="config"]')->text());
        self::assertSame('2', $totals->filter('[data-pack-total="routes"]')->text());
        self::assertSame('1', $totals->filter('[data-pack-total="migrations"]')->text());
    }

    #[DataProvider('localesWith1Argument')]
    public function testASuggestedPluginIsShownApartFromTheMembers(string $locale): void
    {
        $page = $this->open($locale, PackSamples::data());

        $pack = $page->filter('[data-pack="' . PackSamples::REPOSITORY . '"]');
        self::assertCount(1, $pack->filter('[data-pack-suggest="' . PackSamples::AUDIT . '"]'));
        self::assertCount(0, $pack->filter('[data-pack-plugin="' . PackSamples::AUDIT . '"]'));
        self::assertCount(0, $page->filter('[data-pack="' . PackSamples::OTHER_REPOSITORY . '"] [data-pack-suggest]'));
    }

    #[DataProvider('localesWith1Argument')]
    public function testThePluginsShownAreThoseOfTheDataFileAndAddingOneToTheFileAddsItToThePage(string $locale): void
    {
        $data = PackSamples::data();
        $before = $this->open($locale, $data)->filter('[data-pack="' . PackSamples::OTHER_REPOSITORY . '"] [data-pack-plugin]')->each(static fn (Crawler $plugin): string => (string) $plugin->attr('data-pack-plugin'));
        $data = Path::with($data, 'packs.' . PackSamples::OTHER_REPOSITORY . '.plugins', [...PackSamples::otherPack()['plugins'], ['slug' => 'sylius-timeline-plugin', 'bound' => '^1.0', 'role' => ['en' => 'Timeline', 'fr' => 'Chronologie']]]);

        $after = $this->open($locale, $data)->filter('[data-pack="' . PackSamples::OTHER_REPOSITORY . '"] [data-pack-plugin]')->each(static fn (Crawler $plugin): string => (string) $plugin->attr('data-pack-plugin'));

        self::assertSame(['sylius-gpsr-plugin'], $before);
        self::assertSame(['sylius-gpsr-plugin', 'sylius-timeline-plugin'], $after);
    }

    #[DataProvider('localesWith1Argument')]
    public function testThePageOfTheCommittedFileListsExactlyThePluginsOfTheFile(string $locale): void
    {
        $file = \dirname(__DIR__, 3) . '/config/demo/packs.json';
        $decoded = json_decode((string) file_get_contents($file), true, 512, \JSON_THROW_ON_ERROR);
        $expected = [];
        foreach (Path::get(\is_array($decoded) ? $decoded : [], 'packs') as $name => $pack) {
            $expected[(string) $name] = array_column(Path::get(\is_array($pack) ? $pack : [], 'plugins'), 'slug');
        }
        self::ensureKernelShutdown();

        $page = self::createClient()->request('GET', '/' . $locale . '/packs');

        $shown = [];
        foreach ($page->filter('[data-pack]') as $node) {
            $pack = new Crawler($node);
            $shown[(string) $pack->attr('data-pack')] = $pack->filter('[data-pack-plugin]')->each(static fn (Crawler $plugin): string => (string) $plugin->attr('data-pack-plugin'));
        }
        self::assertNotSame([], $expected);
        self::assertSame($expected, $shown, 'the page and config/demo/packs.json list other plugins');
    }

    #[DataProvider('localesWith1Argument')]
    public function testNoPluginKeptOutOfThePacksIsNamedOnThePage(string $locale): void
    {
        self::ensureKernelShutdown();
        $real = self::createClient()->request('GET', '/' . $locale . '/packs')->filter('[data-page="packs"]')->html();
        $sample = $this->open($locale, PackSamples::data())->filter('[data-page="packs"]')->html();

        foreach ([$real, $sample] as $html) {
            foreach (self::KEPT_OUT as $slug) {
                self::assertStringNotContainsString('sylius-' . $slug . '-plugin', $html);
                self::assertStringNotContainsString('tmonier/sylius-' . $slug, $html);
            }
        }
    }

    #[DataProvider('localesWith1Argument')]
    public function testAPluginInNoPackIsSentToTheOneByOneInstallation(string $locale): void
    {
        $page = $this->open($locale, PackSamples::data());

        $single = $page->filter('[data-packs-single]');
        self::assertCount(1, $single);
        self::assertSame('/' . $locale . '/integrer', $single->filter('a[data-packs-integrate-link]')->attr('href'));
        self::assertSame('/' . $locale . '/plugins', $single->filter('a[data-packs-plugins-link]')->attr('href'));
    }

    #[DataProvider('localesWith1Argument')]
    public function testTheGuideAndTheArchitecturePageLeadToThePacks(string $locale): void
    {
        $client = self::createClient();

        $integrate = $client->request('GET', '/' . $locale . '/integrer');
        $architecture = $client->request('GET', '/' . $locale . '/architecture');

        self::assertSame('/' . $locale . '/packs', $integrate->filter('a[data-integrate-packs-link]')->attr('href'));
        self::assertSame('/' . $locale . '/packs', $architecture->filter('a[data-architecture-packs-link]')->attr('href'));
    }

    #[DataProvider('localesWith1Argument')]
    public function testEachPackGivesItsSourcesAndTheDateItWasChecked(string $locale): void
    {
        $page = $this->open($locale, PackSamples::data());

        $sources = $page->filter('[data-pack="' . PackSamples::REPOSITORY . '"] [data-pack-source]');
        self::assertSame(
            ['https://github.com/thibmonier/sylius-pack-ai-headless/blob/main/composer.json', 'https://github.com/thibmonier/sylius-pack-ai-headless/blob/main/README.md'],
            $sources->filter('a')->each(static fn (Crawler $a): string => (string) $a->attr('href')),
            'sorted by address',
        );
        self::assertSame('2026-10-24', $sources->filter('time')->attr('datetime'));
        self::assertCount(0, $sources->filter('[data-pack-stale]'));
    }

    #[DataProvider('localesWith1Argument')]
    public function testALastValueKeptAfterAFailedGenerationIsServedWithItsDate(string $locale): void
    {
        $data = Path::with(PackSamples::data(), 'packs.' . PackSamples::REPOSITORY . '.stale', true);
        $data = Path::with($data, 'packs.' . PackSamples::REPOSITORY . '.checked_at', '2026-10-14');

        $page = $this->open($locale, $data);

        $sources = $page->filter('[data-pack="' . PackSamples::REPOSITORY . '"] [data-pack-source]');
        self::assertSame('2026-10-14', $sources->filter('time')->attr('datetime'));
        self::assertCount(1, $sources->filter('[data-pack-stale]'));
    }

    #[DataProvider('locales')]
    public function testAMissingOrBrokenFileGivesANormalPageWithNothingAvailable(string $locale, string $unavailable): void
    {
        foreach ([null, '{ not json'] as $content) {
            $this->setEnvironment(['DEMO_PACKS_FILE' => null === $content ? sys_get_temp_dir() . '/packs-missing-' . bin2hex(random_bytes(4)) . '.json' : $this->file($content)]);
            self::ensureKernelShutdown();

            $page = self::createClient()->request('GET', '/' . $locale . '/packs');

            self::assertResponseIsSuccessful();
            self::assertCount(0, $page->filter('[data-pack]'));
            self::assertStringContainsString($unavailable, $page->filter('[data-packs-unavailable]')->text());
            self::assertCount(1, $page->filter('[data-packs-single]'), 'the one-by-one installation stays reachable');
        }
    }

    public function testTheTwoLanguagesShowTheSamePacksPluginsStepsAndLinks(): void
    {
        $french = $this->describe($this->open('fr_FR', PackSamples::data()));
        $english = $this->describe($this->open('en_US', PackSamples::data()));

        self::assertSame($french, $english);
        self::assertNotSame([], $french['plugins']);
        self::assertNotSame([], $french['steps']);
    }

    #[DataProvider('localesWith1Argument')]
    public function testTheTextOfThePageCarriesTheCharter(string $locale): void
    {
        $charter = new PublicTextCharter();
        $text = $this->open($locale, PackSamples::data())->filter('[data-page="packs"]')->text();

        self::assertSame([], $charter->commercialWording($text));
        self::assertSame([], $charter->ambiguities($text));
        self::assertDoesNotMatchRegularExpression('/\bPlus\b/', $text, 'no comparison with a commercial offer');
        self::assertDoesNotMatchRegularExpression('/€|\$|\bprix\b|\bprice\b|\bgratuit|\bfree\b/iu', $text, 'no price');
        self::assertDoesNotMatchRegularExpression('/\b(?:better|best|cheaper|mieux|meilleur|moins cher)\b/iu', $text, 'no comparison');
    }

    public function testThePageHasItsOwnDescription(): void
    {
        $client = self::createClient();

        $packs = $client->request('GET', '/fr_FR/packs')->filter('meta[name="description"]')->attr('content');
        $integrate = $client->request('GET', '/fr_FR/integrer')->filter('meta[name="description"]')->attr('content');

        self::assertNotSame($integrate, $packs);
        self::assertStringContainsString('pack', (string) $packs);
    }

    public function testNoFigureIsWrittenInTheTemplateOrTheLabels(): void
    {
        $root = \dirname(__DIR__, 3);

        foreach (['/templates/demo/packs.html.twig', '/translations/packs.fr.yaml', '/translations/packs.en.yaml'] as $file) {
            $content = (string) file_get_contents($root . $file);
            self::assertNotSame('', $content, $file);
            self::assertDoesNotMatchRegularExpression('/\d+\.\d+/', $content, $file . ' states a version by hand');
            self::assertDoesNotMatchRegularExpression('/\b(Sylius|Symfony|PHP) \d/', $content, $file);
            self::assertDoesNotMatchRegularExpression('/tmonier\/sylius/', $content, $file . ' names a package by hand');
        }
    }

    public function testThePageMakesNoOutboundRequest(): void
    {
        $root = \dirname(__DIR__, 3);
        $source = (string) file_get_contents($root . '/src/Demo/Packs/PacksReader.php') . (string) file_get_contents($root . '/src/Demo/Controller/PacksController.php');

        foreach (['HttpClient', 'curl_', 'file_get_contents(\'http', 'Process'] as $call) {
            self::assertStringNotContainsString($call, $source);
        }
    }

    /** @return array{plugins: list<string>, steps: list<string>, links: list<string>} */
    private function describe(Crawler $page): array
    {
        return [
            'plugins' => $page->filter('[data-pack-plugin]')->each(static fn (Crawler $plugin): string => (string) $plugin->attr('data-pack-plugin')),
            'steps' => $page->filter('[data-pack-step]')->each(static fn (Crawler $step): string => (string) $step->attr('data-pack-step')),
            'links' => $page->filter('[data-page="packs"] a')->each(static fn (Crawler $a): string => (string) preg_replace('#^/(fr_FR|en_US)/#', '/{locale}/', (string) $a->attr('href'))),
        ];
    }

    /** @param array<array-key, mixed> $data */
    private function open(string $locale, array $data): Crawler
    {
        $this->setEnvironment(['DEMO_PACKS_FILE' => $this->file((string) json_encode($data, \JSON_THROW_ON_ERROR))]);

        self::ensureKernelShutdown();

        return self::createClient()->request('GET', '/' . $locale . '/packs');
    }

    private function file(string $content): string
    {
        $file = tempnam(sys_get_temp_dir(), 'packs-page') ?: throw new \RuntimeException('no temporary file');
        file_put_contents($file, $content);

        return $this->files[] = $file;
    }

    /** @return iterable<array-key, list<mixed>> */
    public static function localesWith1Argument(): iterable
    {
        return DataSets::firstArguments(self::locales(), 1);
    }
}
