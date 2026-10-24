<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Charter\PublicTextCharter;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-183: the page "Fictitious platform" (/{locale}/pa-fictive): the banner that says the statuses are simulated, without
 * regulatory value and to be verified, the four scenarios of the demo data, the same content in French and in English, no
 * real platform named, and a link from the page "Integrate".
 */
final class FictitiousPlatformPageTest extends WebTestCase
{
    /** Names of real platforms and of the public portal: the page never presents one as a partner (ADR 0016). */
    private const REAL_PLATFORMS = ['Chorus', 'Pennylane', 'Docaposte', 'Cegid', 'Tiime', 'Qonto', 'Yooz', 'Sellsy'];

    /** @return iterable<string, array{string, string, string, string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR', 'simulés', 'sans valeur réglementaire', 'à vérifier'];
        yield 'English' => ['en_US', 'simulated', 'no regulatory value', 'to be verified'];
    }

    #[DataProvider('locales')]
    public function testTheBannerSaysTheStatusesAreSimulatedWithoutRegulatoryValueAndToBeVerified(string $locale, string $simulated, string $value, string $toVerify): void
    {
        $page = self::createClient()->request('GET', '/' . $locale . '/pa-fictive');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $page->filter('h1'));
        $banner = mb_strtolower($page->filter('[data-fictitious-banner]')->text());
        foreach ([$simulated, $value, $toVerify] as $expected) {
            self::assertStringContainsString($expected, $banner);
        }
    }

    #[DataProvider('locales')]
    public function testThePageListsTheFourScenariosOfTheDemoData(string $locale): void
    {
        $page = self::createClient()->request('GET', '/' . $locale . '/pa-fictive');

        self::assertSame(
            ['deposited', 'rejected', 'refused', 'collected'],
            $page->filter('[data-fictitious-scenario]')->each(static fn (Crawler $node): string => (string) $node->attr('data-fictitious-scenario')),
        );
        foreach ($page->filter('[data-fictitious-scenario]') as $scenario) {
            self::assertNotSame('', trim((new Crawler($scenario))->text()));
        }
    }

    public function testTheTwoLanguagesShowTheSameScenariosAndLinks(): void
    {
        $client = self::createClient();
        $describe = static fn (Crawler $page): array => [
            $page->filter('[data-fictitious-scenario]')->each(static fn (Crawler $node): string => (string) $node->attr('data-fictitious-scenario')),
            $page->filter('main a[href]')->each(static fn (Crawler $node): string => (string) preg_replace('~/(?:fr_FR|en_US)/~', '/{locale}/', (string) $node->attr('href'))),
        ];

        self::assertSame($describe($client->request('GET', '/fr_FR/pa-fictive')), $describe($client->request('GET', '/en_US/pa-fictive')));
    }

    #[DataProvider('locales')]
    public function testTheTextCarriesTheCharterAndNamesNoRealPlatform(string $locale): void
    {
        $charter = new PublicTextCharter();
        $text = self::createClient()->request('GET', '/' . $locale . '/pa-fictive')->filter('main')->text();

        self::assertSame([], $charter->commercialWording($text));
        self::assertSame([], $charter->ambiguities($text));
        self::assertDoesNotMatchRegularExpression('/\bPlus\b/', $text, 'no comparison with a commercial offer');
        foreach (self::REAL_PLATFORMS as $name) {
            self::assertStringNotContainsStringIgnoringCase($name, $text);
        }
        self::assertStringContainsStringIgnoringCase('fr_FR' === $locale ? 'PA fictive' : 'fictitious', $text);
    }

    #[DataProvider('locales')]
    public function testThePageIntegrateLinksToIt(string $locale): void
    {
        $client = self::createClient();

        $link = $client->request('GET', '/' . $locale . '/integrer')->filter('a[data-integrate-fictitious-platform]');

        self::assertCount(1, $link);
        self::assertSame('/' . $locale . '/pa-fictive', $link->attr('href'));
        $client->request('GET', (string) $link->attr('href'));
        self::assertResponseIsSuccessful();
    }
}
