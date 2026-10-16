<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-019 — If the B2B channel is closed, the anonymous visitor reaches the home page, the login, the registration,
 * the demo pages and the routes of PunchOut, MCP and the feeds (configured allow-list), and nothing else of the
 * catalogue. The mode is changed inside a transaction that is rolled back.
 */
final class ClosedChannelDemoTest extends WebTestCase
{
    private ?KernelBrowser $client = null;

    protected function tearDown(): void
    {
        if ($this->client instanceof KernelBrowser) {
            $connection = $this->connection($this->client);
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
        }
        parent::tearDown();
    }

    public function testTheExemptRoutesStayReachable(): void
    {
        $client = $this->closedChannelClient();

        foreach (['/fr_FR/', '/fr_FR/login', '/fr_FR/register', '/demo/accounts', '/.well-known/oauth-protected-resource', '/admin/login'] as $path) {
            $client->request('GET', $path);

            self::assertResponseIsSuccessful($path);
        }
    }

    public function testPunchOutMcpAndFeedsAreNotTurnedIntoLoginRedirects(): void
    {
        $client = $this->closedChannelClient();

        foreach (['/punchout/cxml', '/_mcp', '/feeds/google_b2b', '/mcp-oauth/token', '/punchout/transfer'] as $path) {
            $client->request('GET', $path);

            self::assertFalse($client->getResponse()->isRedirect('/fr_FR/login'), $path . ' answered with the login redirect');
            self::assertNotSame(302, $client->getResponse()->getStatusCode(), $path);
        }
    }

    public function testTheCatalogueIsClosedToAnAnonymousVisitor(): void
    {
        $client = $this->closedChannelClient();
        $slug = $this->connection($client)->fetchOne("SELECT t.slug FROM sylius_product p JOIN sylius_product_translation t ON t.translatable_id = p.id AND t.locale = 'fr_FR' WHERE p.enabled = 1 ORDER BY p.id LIMIT 1");
        self::assertIsString($slug);

        foreach (['/fr_FR/products/' . $slug, '/fr_FR/taxons/protection-tete', '/fr_FR/quick-order', '/fr_FR/does-not-exist', '/api/v2/shop/products'] as $path) {
            $client->request('GET', $path);
            $response = $client->getResponse();

            self::assertContains($response->getStatusCode(), [302, 401], $path);
            self::assertStringNotContainsString($slug, (string) $response->getContent(), $path);
        }
    }

    public function testTheHomePageOfAClosedChannelShowsNoProductAndNoMenu(): void
    {
        $client = $this->closedChannelClient();

        $client->request('GET', '/fr_FR/');
        $html = (string) $client->getResponse()->getContent();

        preg_match_all('#href="[^"]*/(?:products|taxons)/[^"]*"#', $html, $links);
        self::assertSame([], $links[0]);
    }

    private function closedChannelClient(): KernelBrowser
    {
        $client = self::createClient();
        $client->disableReboot();
        $this->client = $client;
        $connection = $this->connection($client);
        $connection->beginTransaction();
        $connection->executeStatement("UPDATE sylius_channel SET access_mode = 'closed' WHERE code = 'B2B'");

        return $client;
    }

    private function connection(KernelBrowser $client): Connection
    {
        $connection = $client->getContainer()->get('doctrine.dbal.default_connection');
        self::assertInstanceOf(Connection::class, $connection);

        return $connection;
    }
}
