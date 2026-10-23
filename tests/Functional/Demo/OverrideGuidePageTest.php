<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Charter\PublicTextCharter;
use App\Demo\OverrideGuide\OverrideGuideExamples;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Yaml\Yaml;

/**
 * US-172: the override guide. Four cases (template, form, entity, service), each with a real example of the demo
 * (file and lines, links to the public repository) and a command to check it; copying a whole template comes last.
 */
final class OverrideGuidePageTest extends WebTestCase
{
    private const REPOSITORY = 'https://github.com/thibmonier/sylius-b2b-demo/blob/main/';

    private const CASES = ['template', 'form', 'entity', 'service'];

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR'];
        yield 'English' => ['en_US'];
    }

    #[DataProvider('locales')]
    public function testTheFourCasesEachHaveARealExampleAndACommand(string $locale): void
    {
        $page = $this->page('/' . $locale . '/surcharge');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $page->filter('h1'));
        $cases = $page->filter('section[data-override-case]');
        self::assertSame(self::CASES, $cases->each(static fn (Crawler $node): string => (string) $node->attr('data-override-case')));
        foreach ($cases as $case) {
            $case = new Crawler($case);
            $name = (string) $case->attr('data-override-case');
            self::assertCount(1, $case->filter('h2'), $name);
            self::assertNotSame('', trim($case->filter('pre code')->text()), $name . ' shows an excerpt of the file');
            self::assertMatchesRegularExpression('#^' . preg_quote(self::REPOSITORY, '#') . '[^\#]+\#L\d+-L\d+$#', (string) $case->filter('a[data-override-source]')->attr('href'), $name . ' links to the exact lines');
            self::assertStringStartsWith('bin/console ', trim($case->filter('code[data-override-command]')->text()), $name);
        }
    }

    public function testEveryFileCitedByTheGuideStillExists(): void
    {
        self::ensureKernelShutdown();
        $registry = self::getContainer()->get(OverrideGuideExamples::class);
        self::assertInstanceOf(OverrideGuideExamples::class, $registry);

        self::assertSame([], $registry->brokenReferences(), 'an example of the guide cites a file or lines that no longer exist');
    }

    #[DataProvider('locales')]
    public function testCopyingATemplateIsPresentedLastAsALastResortAfterTheHookAndTheExtension(string $locale): void
    {
        $page = $this->page('/' . $locale . '/surcharge');

        $sequence = $page->filter('section[data-override-case], section[data-override-last-resort]')->each(
            static fn (Crawler $node): string => (string) ($node->attr('data-override-case') ?? 'last-resort'),
        );
        self::assertSame([...self::CASES, 'last-resort'], $sequence, 'the last resort comes after the four cases');
        $lastResort = $page->filter('section[data-override-last-resort]');
        self::assertCount(1, $lastResort->filter('code[data-override-command]'));
        self::assertGreaterThan(0, $lastResort->filter('a[data-override-source]')->count());
        $template = $page->filter('section[data-override-case="template"]');
        self::assertCount(1, $template->filter('a[href="#override-last-resort"]'), 'the template case points to the last resort');
    }

    /** @return iterable<string, array{string, string}> */
    public static function linkingPages(): iterable
    {
        foreach (['fr_FR', 'en_US'] as $locale) {
            yield $locale => [$locale, '/' . $locale . '/integrer'];
        }
    }

    #[DataProvider('linkingPages')]
    public function testTheIntegrationGuideLinksToTheOverrideGuide(string $locale, string $path): void
    {
        $link = $this->page($path)->filter('a[data-integrate-override-link]');

        self::assertCount(1, $link);
        self::assertSame('/' . $locale . '/surcharge', $link->attr('href'));
    }

    public function testBothLanguagesHaveTheSameKeys(): void
    {
        self::assertSame(array_keys($this->texts('fr')), array_keys($this->texts('en')));
    }

    public function testTheTextsHoldNoFigureAndNoSecurityDetail(): void
    {
        foreach (['fr', 'en'] as $language) {
            foreach ($this->texts($language) as $key => $text) {
                self::assertDoesNotMatchRegularExpression('/\d/', $text, $language . ': ' . $key . ' writes a figure by hand');
                foreach (['rate_limiter', 'limiter', 'password', 'mot de passe', 'basic auth', 'secret'] as $term) {
                    self::assertStringNotContainsString($term, mb_strtolower($text), $language . ': ' . $key);
                }
            }
        }
    }

    /** Neutral tone (US-116 charter): no comparison with a commercial offer, no commercial wording. */
    public function testTheTextsRespectTheEditorialCharter(): void
    {
        $charter = new PublicTextCharter();
        foreach (['fr', 'en'] as $language) {
            $all = implode("\n", $this->texts($language));
            self::assertSame([], $charter->commercialWording($all), $language);
            self::assertSame([], $charter->ambiguities($all), $language);
            foreach (['sylius plus', 'enterprise', 'moins cher', 'cheaper', 'meilleur que', 'better than', '€', 'tarif', 'pricing'] as $term) {
                self::assertStringNotContainsString($term, mb_strtolower($all), $language);
            }
        }
    }

    /** @return array<string, string> */
    private function texts(string $language): array
    {
        $file = \dirname(__DIR__, 3) . '/translations/override_guide.' . $language . '.yaml';
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

    private function page(string $path): Crawler
    {
        self::ensureKernelShutdown();

        return self::createClient()->request('GET', $path);
    }
}
