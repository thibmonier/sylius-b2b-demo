<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Charter\PublicTextCharter;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Yaml\Yaml;

/** US-113: the home in three columns, who designed the demo and how it was made (profile and repository links, plain text without profile). */
final class HomeAuthorTest extends WebTestCase
{
    use EnvironmentSwitch;

    private const PROFILE = 'https://www.linkedin.com/in/thibautmonier/';

    private const REPOSITORY = 'https://github.com/thibmonier/sylius-b2b-demo';

    /** @return iterable<string, array{string, string}> */
    public static function locales(): iterable
    {
        yield 'fr' => ['fr_FR', 'Thibaut Monier'];
        yield 'en' => ['en_US', 'Thibaut Monier'];
    }

    #[DataProvider('locales')]
    public function testThreeColumnsAndARoleChoiceOpenTheHome(string $locale, string $author): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => self::PROFILE]);

        $promise = $this->home($locale)->filter('[data-home-promise]');

        self::assertCount(1, $promise);
        self::assertSame(['buy', 'validate', 'integrate'], $promise->filter('[data-promise-column]')->each(static fn (Crawler $column): string => (string) $column->attr('data-promise-column')));
        foreach ($promise->filter('[data-promise-column]') as $column) {
            $column = new Crawler($column);
            self::assertCount(1, $column->filter('h2, h3'));
            self::assertNotSame('', trim($column->filter('p')->text()));
        }
        self::assertGreaterThanOrEqual(1, $this->home($locale)->filter('[data-home-hero] a[data-home-role-choice][href="/demo/accounts"]')->count());
    }

    #[DataProvider('locales')]
    public function testTheAuthorLineIsInTheHeroWithItsProfileLink(string $locale, string $author): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => self::PROFILE]);

        $line = $this->home($locale)->filter('[data-home-hero] [data-home-author-line]');

        self::assertCount(1, $line);
        self::assertStringContainsString($author, $line->text());
        self::assertStringContainsString('25', $line->text());
        self::assertCount(1, $line->filter('a[data-author-profile]'));
        self::assertSame(self::PROFILE, $line->filter('a[data-author-profile]')->attr('href'));
    }

    #[DataProvider('locales')]
    public function testTheAuthorAndMethodSectionLinksTheProfileAndTheRepository(string $locale, string $author): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => self::PROFILE]);

        $section = $this->home($locale)->filter('[data-home-author]');

        self::assertCount(1, $section);
        self::assertCount(2, $section->filter('h2, h3'), 'who designed it, how it was made');
        self::assertStringContainsString($author, $section->text());
        self::assertSame(self::PROFILE, $section->filter('a[data-author-profile]')->attr('href'));
        self::assertSame(self::REPOSITORY, $section->filter('a[data-author-repository]')->attr('href'));
        $method = mb_strtolower($section->filter('[data-author-method]')->text());
        $terms = 'fr_FR' === $locale ? ['tests', 'analyse statique', 'sécurité'] : ['tests', 'static analysis', 'security'];
        foreach ($terms as $term) {
            self::assertStringContainsString($term, $method);
        }
    }

    #[DataProvider('locales')]
    public function testWithoutProfileTheAuthorIsPlainTextAndNothingBreaks(string $locale, string $author): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => '']);

        $home = $this->home($locale);

        self::assertCount(0, $home->filter('a[data-author-profile]'));
        self::assertStringContainsString($author, $home->filter('[data-home-author-line]')->text());
        self::assertStringContainsString($author, $home->filter('[data-home-author]')->text());
        self::assertCount(1, $home->filter('a[data-author-repository]'), 'the repository stays a public proof');
    }

    #[DataProvider('locales')]
    public function testTheHomeAndItsNewTextsRespectTheCharter(string $locale, string $author): void
    {
        $charter = new PublicTextCharter();
        $home = $this->home($locale);
        $text = $home->filter('[data-home-hero], [data-home-promise], [data-home-author]')->each(static fn (Crawler $node): string => $node->text());
        $joined = implode("\n", $text);

        self::assertSame([], $charter->ambiguities($joined));
        self::assertSame([], $charter->commercialWording($joined));
        $plugins = (int) $home->filter('[data-home-author-line]')->attr('data-plugin-count');
        self::assertGreaterThan(0, $plugins);
        self::assertSame([], $charter->unsourcedFigures($joined, [(string) $plugins]));
        self::assertStringNotContainsStringIgnoringCase('Sylius Plus', $joined);
    }

    /** @return iterable<string, array{string}> */
    public static function translationKeys(): iterable
    {
        $root = \dirname(__DIR__, 3);
        foreach (['fr', 'en'] as $language) {
            $messages = Yaml::parseFile($root . '/translations/messages.' . $language . '.yaml');
            foreach (self::flatten(\is_array($messages) ? $messages : [], '') as $key => $text) {
                if (1 === preg_match('/^app\.home_b2b\.(hero|promise|author)\./', $key)) {
                    yield $language . ' ' . $key => [$text];
                }
            }
        }
    }

    #[DataProvider('translationKeys')]
    public function testEveryTextOfTheNewSectionsPassesTheCharter(string $text): void
    {
        $charter = new PublicTextCharter();

        self::assertSame([], $charter->ambiguities($text), $text);
        self::assertSame([], $charter->commercialWording($text), $text);
        self::assertSame([], $charter->unsourcedFigures($text), $text);
    }

    public function testTheAboutPageCarriesTheSameMention(): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => self::PROFILE]);

        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');
        $about = $client->request('GET', '/fr_FR/a-propos')->filter('[data-legal-page="about"] [data-home-author]');

        self::assertCount(1, $about);
        self::assertCount(1, $about->filter('a[data-author-profile]'));
        self::assertCount(1, $about->filter('a[data-author-repository]'));
    }

    /**
     * @param array<mixed> $tree
     *
     * @return array<string, string>
     */
    private static function flatten(array $tree, string $prefix): array
    {
        $flat = [];
        foreach ($tree as $key => $value) {
            if (\is_array($value)) {
                $flat += self::flatten($value, $prefix . (string) $key . '.');

                continue;
            }
            $flat[$prefix . (string) $key] = \is_scalar($value) ? (string) $value : '';
        }

        return $flat;
    }

    private function home(string $locale): Crawler
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');

        return $client->request('GET', '/' . $locale . '/');
    }
}
