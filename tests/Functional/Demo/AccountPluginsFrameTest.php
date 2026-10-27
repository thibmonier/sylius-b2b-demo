<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Tests\Support\IsolatesSharedAccounts;
use App\Tests\Support\Typed;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-203: the pages of the quote, trade-credit and MCP plugins take the frame of "My account" on the B2B channel (menu of the
 * account with the active entry, header of the charter, no menu of the plugin), keep every `data-test` of the plugin, and leave
 * the consumer channel and the refusals of access as they were. The page of consent of the MCP plugin takes the B2B frame
 * without the menu of the account (decision of the 2026-10-27). Run against a database loaded with the `demo` fixtures.
 */
final class AccountPluginsFrameTest extends WebTestCase
{
    use IsolatesSharedAccounts;
    use McpAccess;

    private const CLAIRE = 'claire@acme.example';

    private const ISABELLE = 'isabelle@acme.example';

    private const LEA = 'lea@demo.example';

    /** @return iterable<string, array{string, string, string, string}> account, path, entry of the menu that is active, data-test of the plugin */
    public static function pages(): iterable
    {
        yield 'devis, liste' => [self::CLAIRE, '/fr_FR/account/quotes', 'tmonier_quote_requests', 'data-test-tmonier-quotes'];
        yield 'devis, détail' => [self::CLAIRE, '/fr_FR/account/quotes/Q-2026-001', '', 'data-test-tmonier-quote'];
        yield 'assistants IA' => [self::ISABELLE, '/fr_FR/account/ai-assistants', 'tmonier_b2b_mcp', 'data-test-tmonier-b2b-mcp-no-connection'];
    }

    #[DataProvider('pages')]
    public function testThePageIsInTheFrameOfMyAccount(string $email, string $path, string $active, string $pluginAttribute): void
    {
        $page = $this->open($email, $path);

        self::assertCount(1, $page->filter('[data-test-b2b-account-nav]'), 'the menu of the account');
        self::assertCount(1, $page->filter('[data-test-b2b-account-plugin-page]'), 'the frame of the page');
        self::assertCount(0, $page->filter('.col-md-3 a.link-reset'), 'the menu of the plugin page (core) is gone');
        self::assertCount(1, $page->filter('h1'));
        if ('' !== $active) {
            $current = $page->filter('[data-test-b2b-account-nav-item="' . $active . '"][aria-current="page"]');
            self::assertCount(1, $current, 'the entry of the page is the active one');
        }
        self::assertGreaterThanOrEqual(1, $page->filter('[' . $pluginAttribute . ']')->count(), 'the data-test of the plugin');
    }

    public function testTheTitleOfThePageIsTheHeaderOfTheCharter(): void
    {
        $page = $this->open(self::CLAIRE, '/fr_FR/account/quotes');

        self::assertCount(1, $page->filter('[data-test-b2b-account-plugin-page] .b2b-account__head h1.b2b-account__title'));
        self::assertCount(1, $page->filter('[data-test-b2b-account-plugin-page] .b2b-account__head .b2b-account__identity'), 'the help sentence of the plugin');
    }

    public function testTheListOfQuotesKeepsEveryDataTestOfTheOriginal(): void
    {
        $page = $this->open(self::CLAIRE, '/fr_FR/account/quotes');

        self::assertCount(1, $page->filter('[data-test-tmonier-quotes]'));
        self::assertGreaterThanOrEqual(1, $page->filter('[data-test-tmonier-quote-row]')->count());
        self::assertCount(1, $page->filter('[data-test-tmonier-quote-row="Q-2026-001"] a[href$="/account/quotes/Q-2026-001"]'));
    }

    public function testTheTableOfQuotesScrollsInsideItsOwnRegion(): void
    {
        $page = $this->open(self::CLAIRE, '/fr_FR/account/quotes');

        $region = $page->filter('[data-test-b2b-account-plugin-page] [role="region"][tabindex="0"]');
        self::assertCount(1, $region);
        self::assertNotSame('', trim((string) $region->attr('aria-label')));
        self::assertCount(1, $region->filter('[data-test-tmonier-quotes]'));
    }

    public function testTheConnectionsOfAnAssistantKeepTheirRevocationForm(): void
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $this->mcpAccessToken(self::ISABELLE);
        $this->login($client, self::ISABELLE);

        $page = $client->request('GET', '/fr_FR/account/ai-assistants');

        self::assertResponseIsSuccessful();
        self::assertGreaterThanOrEqual(1, $page->filter('[data-test-tmonier-b2b-mcp-connection]')->count());
        $revoke = $page->filter('form[action*="/revoke"]');
        self::assertGreaterThanOrEqual(1, $revoke->count());
        self::assertNotSame('', (string) $revoke->first()->filter('input[name="_csrf_token"]')->attr('value'));
        self::assertGreaterThanOrEqual(1, $page->filter('[data-test-tmonier-b2b-mcp-revoke]')->count());
    }

    public function testTheConsumerChannelKeepsTheThemeOfTheShop(): void
    {
        $page = $this->open(self::LEA, '/fr_FR/account/quotes', 'B2C');

        self::assertCount(0, $page->filter('[data-test-b2b-account-nav]'));
        self::assertCount(0, $page->filter('[data-test-b2b-account-plugin-page]'));
        self::assertGreaterThanOrEqual(1, $page->filter('.col-md-3 a.link-reset')->count(), 'the menu of the core');
        self::assertCount(1, $page->filter('h1'));
        self::assertStringNotContainsString('b2b-account__title', (string) $page->filter('h1')->attr('class'));
    }

    /** @return iterable<string, array{string}> */
    public static function addresses(): iterable
    {
        yield 'devis' => ['/fr_FR/account/quotes'];
        yield 'paiements à terme' => ['/fr_FR/account/trade-credit'];
        yield 'assistants IA' => ['/fr_FR/account/ai-assistants'];
        yield 'consentement' => ['/fr_FR/account/ai-assistants/authorize'];
    }

    #[DataProvider('addresses')]
    public function testAVisitorWithoutAccountIsSentToTheLoginAndNothingIsRendered(string $path): void
    {
        $client = self::createClient();

        $client->request('GET', $path);

        self::assertResponseRedirects();
        self::assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
        self::assertStringNotContainsString('b2b-account-nav', (string) $client->getResponse()->getContent());
    }

    public function testTheConsentPageHasTheB2bFrameWithoutTheMenuOfTheAccount(): void
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $this->login($client, self::ISABELLE);
        $query = $this->authorizationQuery($client);

        $page = $client->request('GET', '/fr_FR/account/ai-assistants/authorize?' . $query);

        self::assertResponseIsSuccessful();
        self::assertCount(0, $page->filter('[data-test-b2b-account-nav]'), 'no menu of the account on the consent page');
        self::assertCount(1, $page->filter('.b2b-consent'), 'the B2B frame');
        self::assertCount(1, $page->filter('[data-test-tmonier-b2b-mcp-consent]'));
        self::assertCount(1, $page->filter('[data-test-tmonier-b2b-mcp-approve]'));
        self::assertCount(1, $page->filter('[data-test-tmonier-b2b-mcp-deny]'));
        self::assertCount(1, $page->filter('[data-test-tmonier-b2b-mcp-redirect-host]'));
        self::assertCount(1, $page->filter('[data-test-tmonier-b2b-mcp-scopes]'));
        self::assertNotSame('', (string) $page->filter('form input[name="_csrf_token"]')->attr('value'));
    }

    public function testTheConsentPageOfTheConsumerChannelIsNotFramed(): void
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $this->login($client, self::LEA);
        $client->request('GET', '/demo/channel/B2C');
        $query = $this->authorizationQuery($client);

        $client->request('GET', '/fr_FR/account/ai-assistants/authorize?' . $query);

        self::assertStringNotContainsString('b2b-consent', (string) $client->getResponse()->getContent());
    }

    public function testThePartialOfTheStoryKeepsTargetsOf44Pixels(): void
    {
        $scss = (string) file_get_contents(\dirname(__DIR__, 3) . '/assets/shop/styles/_account-plugins.scss');

        self::assertMatchesRegularExpression('/\.btn[^{]*\{[^}]*min-height:\s*2\.75rem/s', $scss, 'buttons of the pages are 44 px targets');
        self::assertStringContainsString('prefers-reduced-motion', $scss);
    }

    private function open(string $email, string $path, string $channel = 'B2B'): Crawler
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $this->login($client, $email);
        $client->request('GET', '/demo/channel/' . $channel);

        $page = $client->request('GET', $path);

        self::assertResponseIsSuccessful();

        return $page;
    }

    /** Registers an assistant the way Claude does, then builds the query of its authorization request. */
    private function authorizationQuery(KernelBrowser $client): string
    {
        $client->request('POST', '/mcp-oauth/register', server: ['CONTENT_TYPE' => 'application/json'], content: (string) json_encode([
            'client_name' => 'Assistant de test',
            'redirect_uris' => ['https://claude.ai/api/mcp/auth_callback'],
            'token_endpoint_auth_method' => 'none',
            'grant_types' => ['authorization_code'],
            'response_types' => ['code'],
        ]));
        self::assertResponseStatusCodeSame(201, (string) $client->getResponse()->getContent());
        $registered = json_decode((string) $client->getResponse()->getContent(), true, 8, \JSON_THROW_ON_ERROR);
        self::assertIsArray($registered);

        return http_build_query([
            'response_type' => 'code',
            'client_id' => Typed::string(Typed::at($registered, 'client_id'), 'client_id'),
            'redirect_uri' => 'https://claude.ai/api/mcp/auth_callback',
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', 'verifier-of-the-test', true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
            'state' => 'test',
        ]);
    }

    private function login(KernelBrowser $client, string $email): void
    {
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer);
        $user = $customer->getUser();
        self::assertNotNull($user);

        $client->loginUser($user, 'shop');
    }
}
