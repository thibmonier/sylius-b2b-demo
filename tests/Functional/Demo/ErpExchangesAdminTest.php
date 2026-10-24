<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Charter\PublicTextCharter;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * US-188: the page "ERP exchanges" of the back office (Integrations menu), read only: the batches sent by the export plugin and the
 * acknowledgements of "ERP Démo (fictif)", with a banner that says the statuses are simulated. Run against a database loaded with the
 * `demo` fixtures, which export the last orders and run the simulator once.
 */
final class ErpExchangesAdminTest extends DemoAdminWebTestCase
{
    private const URL = '/admin/demo/erp';

    /** Names of ERP vendors and products: the page never names one, nor presents one as a partner (ADR 0017). */
    private const ERP_NAMES = ['SAP', 'Odoo', 'Sage', 'Dynamics', 'Business Central', 'Divalto', 'Oracle', 'NetSuite', 'Cegid', 'EBP', 'Sellsy'];

    /** @return iterable<string, array{string, string, string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR', 'simulés', 'ERP Démo (fictif)'];
        yield 'English' => ['en_US', 'simulated', 'Demo ERP (fictitious)'];
    }

    #[DataProvider('locales')]
    public function testTheBannerSaysTheStatusesAreSimulatedAndTheErpIsFictitious(string $locale, string $simulated, string $name): void
    {
        $page = $this->sophieClient($locale)->request('GET', self::URL);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString($simulated, mb_strtolower($page->filter('[data-erp-banner]')->text()));
        self::assertStringContainsString($name, $page->filter('[data-demo-erp]')->text());
    }

    #[DataProvider('locales')]
    public function testTheListShowsTheBatchSentAndItsAcknowledgementWithTheirLines(string $locale): void
    {
        $page = $this->sophieClient($locale)->request('GET', self::URL);

        $out = $page->filter('[data-erp-row][data-erp-direction="out"]');
        $in = $page->filter('[data-erp-row][data-erp-direction="in"]');
        self::assertCount(1, $out, 'the batch exported at the load');
        self::assertCount(1, $in, 'the acknowledgement of the simulator');
        self::assertMatchesRegularExpression('/^orders-\d{8}T\d{6}Z-[0-9a-f]{8}\.csv$/', (string) $out->attr('data-erp-name'));
        self::assertSame('ack-' . $out->attr('data-erp-name'), $in->attr('data-erp-name'));
        $lines = (int) $out->filter('[data-erp-read]')->text();
        self::assertGreaterThan(0, $lines);
        self::assertSame([$lines, $lines, 0], [(int) $in->filter('[data-erp-read]')->text(), (int) $in->filter('[data-erp-accepted]')->text(), (int) $in->filter('[data-erp-rejected]')->text()]);
        self::assertNotSame('', trim($in->filter('time')->text()));
    }

    #[DataProvider('locales')]
    public function testThePageIsReadOnlyAndInTheIntegrationsMenu(string $locale): void
    {
        $client = $this->sophieClient($locale);
        $page = $client->request('GET', self::URL);

        self::assertCount(0, $page->filter('[data-demo-erp] form, [data-demo-erp] button'), 'read only');
        self::assertGreaterThanOrEqual(1, $page->filter('a[href="' . self::URL . '"]')->count(), 'entry of the menu');
        foreach (['POST', 'PUT', 'DELETE'] as $method) {
            $client->request($method, self::URL);
            self::assertResponseStatusCodeSame(405);
        }
    }

    #[DataProvider('locales')]
    public function testTheTextNamesNoErpAndCarriesTheCharter(string $locale): void
    {
        $text = $this->sophieClient($locale)->request('GET', self::URL)->filter('[data-demo-erp]')->text();
        $charter = new PublicTextCharter();

        self::assertSame([], $charter->commercialWording($text));
        self::assertSame([], $charter->ambiguities($text));
        foreach (self::ERP_NAMES as $name) {
            self::assertStringNotContainsStringIgnoringCase($name, $text);
        }
    }
}
