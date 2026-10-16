<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-030: the guide to connect an AI assistant to the demo (page /{locale}/mcp) is the same in French and in English, and
 * what it promises is what the MCP endpoint does: metadata for the OAuth sign-in, the orders of Claire and nobody else's,
 * a refusal without a valid token or for an account that is not approved. Run against a database loaded with the `demo`
 * fixture suite; the one write (an account made pending) is rolled back.
 */
final class McpGuideTest extends WebTestCase
{
    use DatabaseAccess;
    use McpAccess;

    private const CLAIRE = 'claire@acme.example';

    protected function tearDown(): void
    {
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');
        if ($connection instanceof \Doctrine\DBAL\Connection && $connection->isTransactionActive()) {
            $connection->rollBack();
        }
        parent::tearDown();
    }

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR'];
        yield 'English' => ['en_US'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('locales')]
    public function testTheGuideGivesNumberedStepsAndTheAddressOfTheServer(string $locale): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/' . $locale . '/mcp');

        self::assertResponseIsSuccessful();
        self::assertGreaterThanOrEqual(5, $crawler->filter('[data-mcp-step]')->count());
        self::assertSelectorTextContains('[data-mcp-server-url]', 'http://localhost/_mcp');
        self::assertStringContainsString('claire@acme.example', $crawler->filter('[data-mcp-step="3"]')->text());
    }

    public function testTheGuideIsTheSameInBothLanguages(): void
    {
        $client = self::createClient();
        $french = $this->describe($client->request('GET', '/fr_FR/mcp'));
        $english = $this->describe($client->request('GET', '/en_US/mcp'));

        self::assertNotSame('', $french['config']);
        self::assertSame($french['steps'], $english['steps'], 'same number of steps');
        self::assertSame($french['config'], $english['config'], 'same configuration block');
        self::assertSame($french['links'], $english['links'], 'same links');
    }

    public function testTheCopyableConfigurationNamesThisServer(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/en_US/mcp');

        $config = json_decode(trim($crawler->filter('[data-mcp-config]')->text()), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($config);
        self::assertSame('http://localhost/_mcp', $config['mcpServers']['fournitures-pro-demo']['args'][2] ?? null);
    }

    public function testEveryLocalLinkOfTheGuideLeadsSomewhere(): void
    {
        $client = self::createClient();
        $links = $client->request('GET', '/fr_FR/mcp')->filter('[data-mcp-links] a[href^="/"]');
        self::assertGreaterThanOrEqual(3, $links->count());

        foreach ($links->each(static fn (Crawler $link): string => (string) $link->attr('href')) as $href) {
            $client->request('GET', $href);
            self::assertTrue($client->getResponse()->isSuccessful() || $client->getResponse()->isRedirect(), $href . ' answered ' . $client->getResponse()->getStatusCode());
        }
    }

    public function testTheOAuthMetadataTheGuideRelyOnIsPublished(): void
    {
        $client = self::createClient();

        $client->request('GET', '/.well-known/oauth-authorization-server');
        self::assertResponseIsSuccessful();
        $server = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($server);
        self::assertSame(['S256'], $server['code_challenge_methods_supported'] ?? null);
        self::assertArrayHasKey('registration_endpoint', $server, 'no client ID to type: the assistant registers itself');
        self::assertStringEndsWith('/mcp-oauth/authorize', (string) ($server['authorization_endpoint'] ?? ''));

        $client->request('GET', '/.well-known/oauth-protected-resource/_mcp');
        self::assertResponseIsSuccessful();
        $resource = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($resource);
        self::assertIsString($resource['resource'] ?? null);
        self::assertStringEndsWith('/_mcp', $resource['resource']);
    }

    public function testTheAssistantOfClaireListsHerOrdersAndNobodyElses(): void
    {
        $client = self::createClient();
        $token = $this->mcpAccessToken(self::CLAIRE);

        $answer = $this->mcpTool($client, $token, 'list_my_orders');

        self::assertIsArray($answer['orders']);
        $numbers = array_column($answer['orders'], 'number');
        $expected = $this->orderNumbers('c.email = ?');
        self::assertNotSame([], $expected);
        self::assertEqualsCanonicalizing($expected, $numbers);
        self::assertSame([], array_intersect($this->orderNumbers('c.email <> ?'), $numbers), 'the orders of the other customers are not exposed');
    }

    /** @return iterable<string, array{string|null}> */
    public static function missingOrExpiredTokens(): iterable
    {
        yield 'no token' => [null];
        yield 'expired token' => ['expired'];
        yield 'unknown token' => ['0123456789abcdef'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('missingOrExpiredTokens')]
    public function testWithoutAValidTokenTheEndpointExposesNothingAndPointsToTheSignIn(?string $kind): void
    {
        $client = self::createClient();
        $token = match ($kind) {
            'expired' => $this->mcpAccessToken(self::CLAIRE, '-5 minutes'),
            default => $kind,
        };

        $this->mcpCall($client, $token, 'tools/list');

        self::assertResponseStatusCodeSame(401);
        self::assertStringContainsString('resource_metadata=', (string) $client->getResponse()->headers->get('WWW-Authenticate'));
        $body = (string) $client->getResponse()->getContent();
        self::assertStringNotContainsString('orders', $body);
        self::assertStringNotContainsString('"result"', $body);
    }

    public function testAnAccountThatIsNotApprovedIsRefusedAndTheGuideSaysSo(): void
    {
        $client = self::createClient();
        $client->disableReboot();
        $this->db()->beginTransaction();
        $token = $this->mcpAccessToken('marc@techparts.example');
        $this->db()->executeStatement("UPDATE sylius_customer SET approval_status = 'pending' WHERE email = ?", ['marc@techparts.example']);

        $this->mcpCall($client, $token, 'initialize', ['protocolVersion' => '2025-03-26', 'capabilities' => new \stdClass(), 'clientInfo' => ['name' => 'demo-test', 'version' => '1.0']]);

        self::assertResponseStatusCodeSame(403);
        $answer = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($answer);
        self::assertSame('access_denied', $answer['error'] ?? null);
        foreach (['fr_FR', 'en_US'] as $locale) {
            self::assertStringContainsString('access_denied', $client->request('GET', '/' . $locale . '/mcp')->filter('[data-mcp-unapproved]')->text(), $locale);
        }
    }

    public function testThePluginsPageLinksToTheGuide(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/fr_FR/plugins');

        $link = $crawler->filter('#plugin-sylius-b2b-mcp-plugin [data-plugin-guide-link]');
        self::assertCount(1, $link);
        self::assertSame('/fr_FR/mcp', $link->attr('href'));
    }

    /** @return list<string> the numbers of the placed orders of Claire (`c.email = ?`) or of everybody else (`c.email <> ?`) */
    private function orderNumbers(string $condition): array
    {
        $numbers = $this->db()->fetchFirstColumn("SELECT o.number FROM sylius_order o JOIN sylius_customer c ON c.id = o.customer_id WHERE o.state <> 'cart' AND " . $condition, [self::CLAIRE]);

        return array_map(static function (mixed $number): string {
            \assert(\is_string($number));

            return $number;
        }, $numbers);
    }

    /** @return array{steps: int, config: string, links: list<string>} */
    private function describe(Crawler $page): array
    {
        return [
            'steps' => $page->filter('[data-mcp-step]')->count(),
            'config' => trim($page->filter('[data-mcp-config]')->count() > 0 ? $page->filter('[data-mcp-config]')->text() : ''),
            'links' => $page->filter('[data-mcp-links] a')->each(static fn (Crawler $a): string => (string) preg_replace('#^/[a-z]{2}_[A-Z]{2}/#', '/{locale}/', (string) $a->attr('href'))),
        ];
    }
}
