<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Tests\Support\DataSets;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Yaml\Yaml;

/**
 * US-107: the page "Integrate these plugins in your project": three plain formulas, the questions asked first, a typical
 * architecture and indicative lead times, in French and English. No price, no comparison; the contact button comes from
 * the configured contact URL and disappears without it.
 */
final class IntegratePageTest extends WebTestCase
{
    use EnvironmentSwitch;

    private const CONTACT = 'https://exemple.test/contact';

    /** @return iterable<string, array{string, string, string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR', 'Écrire un message', 'Sylius 2.3'];
        yield 'English' => ['en_US', 'Write a message', 'Sylius 2.3'];
    }

    #[DataProvider('locales')]
    public function testThePageIsASelfServiceGuideWithPrerequisitesInstallationQuestionsAndArchitecture(string $locale, string $button, string $firstPrerequisite): void
    {
        $page = $this->page('/' . $locale . '/integrer');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $page->filter('h1'));
        $prerequisites = $page->filter('[data-integrate-prerequisites] li');
        self::assertCount(4, $prerequisites);
        self::assertStringContainsString($firstPrerequisite, $prerequisites->first()->text());
        $steps = $page->filter('ol[data-integrate-install] > li');
        self::assertSame(['require', 'bundle', 'config', 'routes', 'migrations', 'assets'], $steps->each(static fn (Crawler $node): string => (string) $node->attr('data-integrate-step')));
        $commands = $page->filter('[data-integrate-install] pre')->each(static fn (Crawler $node): string => $node->text());
        $code = implode("\n", $commands);
        foreach (['composer require tmonier/sylius-quick-order-plugin', 'TmonierSyliusQuickOrderPlugin::class', '@TmonierSyliusQuickOrderPlugin/config/routes.yaml', 'bin/console doctrine:migrations:migrate', 'bin/console assets:install'] as $expected) {
            self::assertStringContainsString($expected, $code);
        }
        $questions = $page->filter('details[data-integrate-question]');
        self::assertSame(['erp', 'punchout', 'e-invoicing', 'volume'], $questions->each(static fn (Crawler $node): string => (string) $node->attr('data-integrate-question')));
        foreach ($questions as $question) {
            self::assertNotSame('', trim((new Crawler($question))->filter('summary')->text()));
        }
        $architecture = $page->filter('svg[data-integrate-architecture][role="img"]');
        self::assertCount(1, $architecture);
        self::assertNotSame('', trim($architecture->filter('title')->text()));
    }

    #[DataProvider('localesWith1Argument')]
    public function testEveryPluginLinksToItsRepositoryAndItsReadme(string $locale): void
    {
        $repositories = $this->page('/' . $locale . '/integrer')->filter('[data-integrate-repository]');

        self::assertGreaterThanOrEqual(20, $repositories->count());
        $first = $repositories->first();
        $slug = (string) $first->attr('data-integrate-repository');
        self::assertSame('https://github.com/thibmonier/' . $slug, $first->filter('a[data-integrate-github]')->attr('href'));
        self::assertSame('https://github.com/thibmonier/' . $slug . '#readme', $first->filter('a[data-integrate-readme]')->attr('href'));
        self::assertStringContainsString('tmonier/' . $slug, $first->filter('code')->text());
    }

    /** US-108 (2026-10-21): the page describes, it does not offer a service: no contact button, even when a contact URL is configured. */
    #[DataProvider('localesWith1Argument')]
    public function testThePageHasNoContactButtonEvenWithAContactUrl(string $locale): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => '', 'DEMO_CONTACT_URL' => self::CONTACT]);

        self::assertCount(0, $this->page('/' . $locale . '/integrer')->filter('[data-integrate-contact]'));
    }

    public function testWithoutContactUrlTheRestOfThePageStays(): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => '', 'DEMO_CONTACT_URL' => '']);

        $page = $this->page('/fr_FR/integrer');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $page->filter('[data-integrate-contact]'));
        self::assertCount(6, $page->filter('[data-integrate-step]'));
        self::assertCount(4, $page->filter('[data-integrate-question]'));
    }

    public function testThePageHasItsOwnDescription(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $page = $client->request('GET', '/fr_FR/integrer');

        self::assertStringContainsString('intégr', mb_strtolower((string) $page->filter('meta[name="description"]')->attr('content')));
    }

    /** @return iterable<string, array{string, string}> */
    public static function linkingPages(): iterable
    {
        foreach (['fr_FR', 'en_US'] as $locale) {
            yield $locale . ' footer' => [$locale, 'footer [data-demo-footer] a[href="/' . $locale . '/integrer"]'];
            yield $locale . ' plugins' => [$locale, 'a[data-plugins-integrate-link][href="/' . $locale . '/integrer"]'];
        }
    }

    #[DataProvider('linkingPages')]
    public function testTheFooterAndThePluginsPageLinkToThePage(string $locale, string $selector): void
    {
        $path = str_starts_with($selector, 'footer') ? '/' . $locale . '/' : '/' . $locale . '/plugins';

        self::assertCount(1, $this->page($path)->filter($selector), $selector);
    }

    #[DataProvider('localesWith1Argument')]
    public function testThePageGivesNoPriceAndNoComparison(string $locale): void
    {
        $text = mb_strtolower($this->page('/' . $locale . '/integrer')->filter('main')->text());

        self::assertDoesNotMatchRegularExpression('/[€$£]|\d\s*(k€|euros?|eur|usd)\b/u', $text);
        self::assertStringNotContainsString('sylius plus', $text);
    }

    /** Charter check (US-116): no forbidden wording in the texts of the page, in both languages. */
    public function testTheTextsOfThePageRespectTheEditorialCharter(): void
    {
        foreach (['fr', 'en'] as $language) {
            $translations = Yaml::parseFile($this->translationFile($language));
            self::assertIsArray($translations);
            self::assertSame([], $this->forbiddenIn($this->flatten($translations)), $language);
        }
    }

    public function testTheCharterCheckDetectsAForbiddenTerm(): void
    {
        $found = $this->forbiddenIn(['formula.intro' => 'Moins cher et meilleur que Sylius Plus.']);

        self::assertContains('formula.intro', array_keys($found));
    }

    /**
     * @param array<string, string> $texts
     *
     * @return array<string, string> key of the text => forbidden term found
     */
    private function forbiddenIn(array $texts): array
    {
        $forbidden = ['votre projet', 'your project', 'formule', 'formula', 'sous contrat', 'under contract', 'délai', 'lead time', 'prestation', 'sylius plus', 'elesto', 'moins cher', 'cheaper', 'meilleur que', 'better than', 'leader', 'gratuit', 'free of charge', 'garanti', 'guaranteed', '€', 'tarif', 'pricing'];
        $found = [];
        foreach ($texts as $key => $text) {
            foreach ($forbidden as $term) {
                if (str_contains(mb_strtolower($text), $term)) {
                    $found[$key] = $term;
                }
            }
        }

        return $found;
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

    private function translationFile(string $language): string
    {
        $file = \dirname(__DIR__, 3) . '/translations/integrate.' . $language . '.yaml';
        self::assertFileExists($file);

        return $file;
    }

    private function page(string $path): Crawler
    {
        self::ensureKernelShutdown();

        return self::createClient()->request('GET', $path);
    }

    /** @return iterable<array-key, list<mixed>> */
    public static function localesWith1Argument(): iterable
    {
        return DataSets::firstArguments(self::locales(), 1);
    }
}
