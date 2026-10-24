<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Channel\DemoChannelContext;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

/**
 * US-193: the `demo` fixtures place orders in the outbox of the webhook plugin and run the export once loaded; the page
 * "Flat-file exchange" shows the real file, for the channel of the visitor. In the test environment the exchange directory is
 * the one of the cache of the worker, never `var/erp-exchange`.
 */
final class ErpFlatfileSampleFixtureTest extends WebTestCase
{
    use DatabaseAccess;

    public function testTheFixturesPlacePlacedOrdersInTheOutboxWithDemoAddressesOnly(): void
    {
        self::bootKernel();

        self::assertGreaterThan(0, $this->intValue("SELECT COUNT(*) FROM tmonier_webhook_outbox_event WHERE type = 'order.placed' AND channel_code = 'B2B'"), 'the orders of the demo are on the B2B channel; the B2C channel has none');
        self::assertSame(0, $this->intValue("SELECT COUNT(*) FROM tmonier_webhook_outbox_event WHERE type = 'order.placed' AND payload NOT LIKE '%.example%'"), 'fictitious addresses only');
        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM tmonier_webhook_delivery'), 'the fixtures queue no webhook delivery');
    }

    public function testTheExportRunAtTheLoadWroteToTheExchangeDirectoryOfTheTestAndNotToVar(): void
    {
        self::bootKernel();
        $directory = self::getContainer()->getParameter('tmonier_erp_flatfile.exchange_directory');

        self::assertIsString($directory);
        self::assertStringNotContainsString('/var/erp-exchange', $directory, 'the tests never write to the exchange directory of the demo');
        $cacheDirectory = self::getContainer()->getParameter('kernel.cache_dir');
        self::assertIsString($cacheDirectory);
        self::assertNotSame('', $cacheDirectory);
        self::assertSame(0, strpos($directory, $cacheDirectory), 'the exchange directory of the tests is in the cache of the worker');
        self::assertNotSame([], glob($directory . '/orders-*.csv'), 'one file produced at the load');
        self::assertSame($this->intValue("SELECT COUNT(*) FROM tmonier_webhook_outbox_event WHERE type = 'order.placed'"), $this->intValue('SELECT COUNT(*) FROM tmonier_erp_flatfile_exported_event'), 'every placed order of the fixtures is exported');
    }

    public function testThePageShowsTheLinesOfTheB2bChannel(): void
    {
        $client = self::createClient();
        $client->getCookieJar()->set(new Cookie(DemoChannelContext::COOKIE, DemoChannelContext::B2B));
        $page = $client->request('GET', '/fr_FR/echange-fichier');

        self::assertResponseIsSuccessful();
        $lines = explode("\n", trim($page->filter('[data-erp-sample]')->text(null, false)));
        self::assertGreaterThan(1, \count($lines), 'the header and at least one line');
        self::assertLessThanOrEqual(4, \count($lines), 'the header and three lines at most');
        foreach (\array_slice($lines, 1) as $line) {
            $fields = explode(';', $line);
            self::assertSame('B2B', $this->db()->fetchOne('SELECT channel_code FROM tmonier_webhook_outbox_event WHERE id = ?', [$fields[1]]));
            self::assertMatchesRegularExpression('/^.\*\*\*@[^@\s]+\.example$/', $fields[4]);
        }
        self::assertStringNotContainsString('erp-exchange', (string) $client->getResponse()->getContent(), 'no internal path');
    }

    public function testTheB2cChannelHasNoOrderAndGetsANeutralMessage(): void
    {
        $client = self::createClient();
        $client->getCookieJar()->set(new Cookie(DemoChannelContext::COOKIE, DemoChannelContext::B2C));
        $page = $client->request('GET', '/en_US/echange-fichier');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $page->filter('[data-erp-sample]'));
        self::assertCount(1, $page->filter('[data-erp-sample-status="other_channel"]'));
        self::assertStringNotContainsString('erp-exchange', (string) $client->getResponse()->getContent());
    }
}
