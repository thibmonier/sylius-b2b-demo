<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

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
        yield 'French' => ['fr_FR', 'Échanger sur votre projet', 'Cadrage'];
        yield 'English' => ['en_US', 'Discuss your project', 'Scoping'];
    }

    #[DataProvider('locales')]
    public function testThePageShowsFormulasQuestionsArchitectureAndLeadTimes(string $locale, string $button, string $firstFormula): void
    {
        $page = $this->page('/' . $locale . '/integrer');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $page->filter('h1'));
        $formulas = $page->filter('[data-integrate-formula]');
        self::assertSame(['scoping', 'integration', 'support'], $formulas->each(static fn (Crawler $node): string => (string) $node->attr('data-integrate-formula')));
        self::assertStringContainsString($firstFormula, $formulas->first()->text());
        $questions = $page->filter('details[data-integrate-question]');
        self::assertSame(['erp', 'punchout', 'e-invoicing', 'volume'], $questions->each(static fn (Crawler $node): string => (string) $node->attr('data-integrate-question')));
        foreach ($questions as $question) {
            self::assertNotSame('', trim((new Crawler($question))->filter('summary')->text()));
        }
        $architecture = $page->filter('svg[data-integrate-architecture][role="img"]');
        self::assertCount(1, $architecture);
        self::assertNotSame('', trim($architecture->filter('title')->text()));
        self::assertCount(3, $page->filter('[data-integrate-delay]'));
        self::assertCount(1, $page->filter('[data-integrate-support-note]'));
    }

    #[DataProvider('locales')]
    public function testTheContactButtonLeadsToTheContactPageWithTheTrackingOfThePage(string $locale, string $button): void
    {
        $this->setEnvironment(['DEMO_BOOKING_URL' => '', 'DEMO_CONTACT_URL' => self::CONTACT]);

        $link = $this->page('/' . $locale . '/integrer')->filter('a[data-integrate-contact]');

        self::assertCount(1, $link);
        self::assertSame($button, trim($link->filter('[data-integrate-contact-label]')->text()));
        self::assertSame(self::CONTACT . '?utm_source=demo-b2b&utm_medium=cta&utm_content=integrate', $link->attr('href'));
        self::assertSame('noopener noreferrer', $link->attr('rel'));
    }

    public function testWithoutContactUrlTheButtonIsHiddenAndTheRestOfThePageStays(): void
    {
        $this->setEnvironment(['DEMO_BOOKING_URL' => '', 'DEMO_CONTACT_URL' => '']);

        $page = $this->page('/fr_FR/integrer');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $page->filter('[data-integrate-contact]'));
        self::assertCount(3, $page->filter('[data-integrate-formula]'));
        self::assertCount(4, $page->filter('[data-integrate-question]'));
    }

    public function testThePageStaysOutOfTheSearchEnginesAndHasItsOwnDescription(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $page = $client->request('GET', '/fr_FR/integrer');

        self::assertSame('noindex, nofollow', $client->getResponse()->headers->get('X-Robots-Tag'));
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

    #[DataProvider('locales')]
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
        $forbidden = ['sylius plus', 'elesto', 'moins cher', 'cheaper', 'meilleur que', 'better than', 'leader', 'gratuit', 'free of charge', 'garanti', 'guaranteed', '€', 'tarif', 'pricing'];
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
}
