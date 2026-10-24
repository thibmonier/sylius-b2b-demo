<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Charter\PublicTextCharter;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Tmonier\SyliusErpFlatfilePlugin\Contract\OrderLinesCsv;

/**
 * US-190: the page "Flat-file exchange" (/{locale}/echange-fichier): the columns of the contract, an excerpt of a file produced
 * from the placed orders of the demo, the same content in French and in English, no personal data and no ERP named.
 */
final class ErpFlatfilePageTest extends WebTestCase
{
    /** Names of ERP vendors and products: the page never names one, nor presents one as a partner (ADR 0017). */
    private const ERP_NAMES = ['SAP', 'Odoo', 'Sage', 'Dynamics', 'Business Central', 'Divalto', 'Oracle', 'NetSuite', 'Cegid', 'EBP', 'Sellsy'];

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR'];
        yield 'English' => ['en_US'];
    }

    #[DataProvider('locales')]
    public function testThePageShowsEveryColumnOfTheContract(string $locale): void
    {
        $page = self::createClient()->request('GET', '/' . $locale . '/echange-fichier');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $page->filter('h1'));
        self::assertSame(
            OrderLinesCsv::HEADER,
            $page->filter('[data-erp-column]')->each(static fn (Crawler $node): string => (string) $node->attr('data-erp-column')),
        );
        foreach ($page->filter('[data-erp-column]') as $column) {
            self::assertNotSame('', trim((new Crawler($column))->text()));
        }
    }

    #[DataProvider('locales')]
    public function testTheExcerptIsAFileOfTheContractWithoutPersonalData(string $locale): void
    {
        $page = self::createClient()->request('GET', '/' . $locale . '/echange-fichier');

        $excerpt = trim($page->filter('[data-erp-sample]')->text(null, false));
        $lines = explode("\n", $excerpt);
        self::assertSame(implode(';', OrderLinesCsv::HEADER), $lines[0]);
        self::assertGreaterThan(1, \count($lines));
        foreach (\array_slice($lines, 1) as $line) {
            $fields = explode(';', $line);
            self::assertCount(10, $fields);
            self::assertMatchesRegularExpression('/^.\*\*\*@[^@\s]+\.example$/', $fields[4], 'masked address on a .example domain');
        }
    }

    public function testTheTwoLanguagesShowTheSameColumnsExcerptAndLinks(): void
    {
        $client = self::createClient();
        $describe = static fn (Crawler $page): array => [
            $page->filter('[data-erp-column]')->each(static fn (Crawler $node): string => (string) $node->attr('data-erp-column')),
            trim($page->filter('[data-erp-sample]')->text(null, false)),
            $page->filter('main a[href]')->each(static fn (Crawler $node): string => (string) preg_replace('~/(?:fr_FR|en_US)/~', '/{locale}/', (string) $node->attr('href'))),
        ];

        self::assertSame($describe($client->request('GET', '/fr_FR/echange-fichier')), $describe($client->request('GET', '/en_US/echange-fichier')));
    }

    #[DataProvider('locales')]
    public function testTheTextCarriesTheCharterAndNamesNoErp(string $locale): void
    {
        $charter = new PublicTextCharter();
        $text = self::createClient()->request('GET', '/' . $locale . '/echange-fichier')->filter('main')->text();

        self::assertSame([], $charter->commercialWording($text));
        self::assertSame([], $charter->ambiguities($text));
        self::assertDoesNotMatchRegularExpression('/\bPlus\b/', $text, 'no comparison with a commercial offer');
        foreach (self::ERP_NAMES as $name) {
            self::assertStringNotContainsStringIgnoringCase($name, $text);
        }
    }

    #[DataProvider('locales')]
    public function testThePageIntegrateLinksToIt(string $locale): void
    {
        $client = self::createClient();
        $link = $client->request('GET', '/' . $locale . '/integrer')->filter('[data-integrate-erp-flatfile]');

        self::assertCount(1, $link);
        self::assertSame('/' . $locale . '/echange-fichier', $link->attr('href'));
        $client->request('GET', (string) $link->attr('href'));
        self::assertResponseIsSuccessful();
    }
}
