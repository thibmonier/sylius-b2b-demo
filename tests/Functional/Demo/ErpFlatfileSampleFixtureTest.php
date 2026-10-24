<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Channel\DemoChannelContext;
use PHPUnit\Framework\Attributes\DataProvider;
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

    /** @return iterable<string, array{string}> */
    public static function channels(): iterable
    {
        yield 'B2B' => [DemoChannelContext::B2B];
        yield 'B2C' => [DemoChannelContext::B2C];
    }

    public function testTheFixturesPlaceOrdersOfBothChannelsInTheOutboxWithDemoAddressesOnly(): void
    {
        self::bootKernel();

        self::assertSame(['B2B', 'B2C'], array_map('strval', $this->db()->fetchFirstColumn("SELECT DISTINCT channel_code FROM tmonier_webhook_outbox_event WHERE type = 'order.placed' ORDER BY channel_code")));
        self::assertSame(0, $this->intValue("SELECT COUNT(*) FROM tmonier_webhook_outbox_event WHERE type = 'order.placed' AND payload NOT LIKE '%.example%'"), 'fictitious addresses only');
        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM tmonier_webhook_delivery'), 'the fixtures queue no webhook delivery');
    }

    public function testTheExportRunAtTheLoadWroteToTheExchangeDirectoryOfTheTestAndNotToVar(): void
    {
        self::bootKernel();
        $directory = self::getContainer()->getParameter('tmonier_erp_flatfile.exchange_directory');

        self::assertIsString($directory);
        self::assertStringNotContainsString('/var/erp-exchange', $directory, 'the tests never write to the exchange directory of the demo');
        self::assertStringStartsWith(self::getContainer()->getParameter('kernel.cache_dir'), $directory);
        self::assertNotSame([], glob($directory . '/orders-*.csv'), 'one file produced at the load');
        self::assertSame($this->intValue("SELECT COUNT(*) FROM tmonier_webhook_outbox_event WHERE type = 'order.placed'"), $this->intValue('SELECT COUNT(*) FROM tmonier_erp_flatfile_exported_event'), 'every placed order of the fixtures is exported');
    }

    #[DataProvider('channels')]
    public function testThePageShowsTheLinesOfTheChannelOfTheVisitor(string $channel): void
    {
        $client = self::createClient();
        $client->getCookieJar()->set(new Cookie(DemoChannelContext::COOKIE, $channel));
        $page = $client->request('GET', '/fr_FR/echange-fichier');

        self::assertResponseIsSuccessful();
        $lines = explode("\n", trim($page->filter('[data-erp-sample]')->text(null, false)));
        self::assertGreaterThan(1, \count($lines), 'the header and at least one line');
        self::assertLessThanOrEqual(4, \count($lines), 'the header and three lines at most');
        foreach (\array_slice($lines, 1) as $line) {
            $fields = explode(';', $line);
            self::assertSame($channel, $this->db()->fetchOne('SELECT channel_code FROM tmonier_webhook_outbox_event WHERE id = ?', [$fields[1]]), 'a line of the channel of the visitor');
            self::assertMatchesRegularExpression('/^.\*\*\*@[^@\s]+\.example$/', $fields[4]);
        }
        self::assertStringNotContainsString('erp-exchange', (string) $client->getResponse()->getContent(), 'no internal path');
    }
}
