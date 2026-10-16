<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use App\Demo\Channel\DemoChannelContext;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

/**
 * US-019 — the B2B channel hides its prices and the purchase from visitors who are not signed in, on the pages
 * and in the shop API; the B2C channel stays open. Asserted on the rendered pages and responses.
 */
final class ChannelAccessSmokeTest extends WebTestCase
{
    /** A price as rendered by the shop in French or English ("12,50 €", "€12.50", "12.50 EUR"). */
    private const PRICE = '/\d[,.]\d\d(?:\s|&nbsp;|\x{00A0}|\x{202F})*(?:€|&euro;|EUR)|(?:€|&euro;)(?:\s|&nbsp;|\x{00A0})*\d/u';

    private const BUY_BUTTON = 'add_to_cart_form';

    public function testAnAnonymousB2bVisitorSeesNoPriceAndNoBuyButtonOnAnyPage(): void
    {
        $client = self::createClient();
        $products = $this->products($client);

        $pages = ['/fr_FR/', '/en_US/', '/fr_FR/quick-order', '/fr_FR/plugins', '/fr_FR/login', '/fr_FR/register', '/demo/accounts'];
        foreach ($products as $product) {
            $pages[] = '/fr_FR/products/' . $product['slug'];
            $pages[] = '/fr_FR/taxons/' . $product['taxon'];
        }

        foreach (array_unique($pages) as $page) {
            $client->request('GET', $page);
            $html = (string) $client->getResponse()->getContent();

            self::assertResponseIsSuccessful($page);
            self::assertDoesNotMatchRegularExpression(self::PRICE, $html, $page);
            self::assertStringNotContainsString(self::BUY_BUTTON, $html, $page);
        }
    }

    public function testTheProductPageInvitesAnAnonymousVisitorToSignIn(): void
    {
        $client = self::createClient();
        $slug = $this->products($client)[0]['slug'];

        $crawler = $client->request('GET', '/fr_FR/products/' . $slug);

        self::assertGreaterThan(0, $crawler->filter('[data-test-tmonier-prices-hidden] a[href$="/fr_FR/login"]')->count());
        self::assertStringContainsString('Connectez-vous pour voir vos prix', $crawler->filter('[data-test-tmonier-prices-hidden]')->first()->text());
    }

    public function testASignedInCustomerSeesThePricesAndTheBuyButton(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');
        $slug = $this->products($client)[0]['slug'];

        $client->request('GET', '/fr_FR/products/' . $slug);
        $html = (string) $client->getResponse()->getContent();

        self::assertResponseIsSuccessful();
        self::assertMatchesRegularExpression(self::PRICE, $html);
        self::assertStringContainsString(self::BUY_BUTTON, $html);
        self::assertStringNotContainsString('data-test-tmonier-prices-hidden', $html);
    }

    public function testTheConsumerChannelStaysOpen(): void
    {
        $client = self::createClient();
        $client->getCookieJar()->set(new Cookie(DemoChannelContext::COOKIE, DemoChannelContext::B2C));
        $slug = $this->products($client)[0]['slug'];

        $client->request('GET', '/fr_FR/products/' . $slug);
        $html = (string) $client->getResponse()->getContent();

        self::assertMatchesRegularExpression(self::PRICE, $html);
        self::assertStringContainsString(self::BUY_BUTTON, $html);
        $client->request('GET', '/fr_FR/cart/');
        self::assertResponseIsSuccessful();
    }

    public function testTheShopApiReturnsNoPriceToAnAnonymousB2bVisitor(): void
    {
        $client = self::createClient();
        $product = $this->products($client)[0];

        foreach (['/api/v2/shop/products', '/api/v2/shop/products/' . $product['code'], '/api/v2/shop/product-variants/' . $product['variant'], '/api/v2/shop/product-variants'] as $url) {
            $client->request('GET', $url, server: ['HTTP_ACCEPT' => 'application/ld+json']);
            self::assertResponseIsSuccessful($url);
            $data = json_decode((string) $client->getResponse()->getContent(), true);
            self::assertIsArray($data);
            unset($data['hydra:search']);
            $keys = $this->keysOf($data);
            foreach (['price', 'originalPrice', 'lowestPriceBeforeDiscount', 'appliedPromotions'] as $forbidden) {
                self::assertNotContains($forbidden, $keys, $url);
            }
        }
    }

    public function testTheTierPricesApiIsRefusedToAnAnonymousB2bVisitorButOpenToACustomer(): void
    {
        $client = self::createClient();
        $url = '/api/v2/shop/product-variants/' . $this->products($client)[0]['variant'] . '/tier-prices';

        $client->request('GET', $url, server: ['HTTP_ACCEPT' => 'application/ld+json']);
        self::assertResponseStatusCodeSame(401);
        self::assertStringNotContainsString('unitPrice', (string) $client->getResponse()->getContent());
    }

    public function testAnAnonymousVisitorCannotOpenACartOrBuyThroughTheApi(): void
    {
        $client = self::createClient();

        $client->request('POST', '/api/v2/shop/orders', server: ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json'], content: '{"localeCode":"fr_FR"}');
        self::assertResponseStatusCodeSame(401);
    }

    public function testTheCartTheCheckoutAndTheQuoteFormAskAnAnonymousVisitorToSignIn(): void
    {
        $client = self::createClient();

        foreach (['/fr_FR/cart/', '/fr_FR/checkout/address', '/fr_FR/quote-request'] as $path) {
            $client->request('GET', $path);
            self::assertResponseRedirects('/fr_FR/login', null, $path);
        }
    }

    public function testTheResponsesOfTheB2bChannelAreNotStoredByASharedCache(): void
    {
        $client = self::createClient();
        $client->request('GET', '/fr_FR/');

        $headers = $client->getResponse()->headers;
        self::assertTrue($headers->hasCacheControlDirective('private'));
        self::assertTrue($headers->hasCacheControlDirective('no-store'));
    }

    public function testTheProductFeedsOfTheB2bChannelNeedAnAccessToken(): void
    {
        $client = self::createClient();
        $connection = $client->getContainer()->get('doctrine.dbal.default_connection');
        self::assertInstanceOf(Connection::class, $connection);

        $without = $connection->fetchFirstColumn("SELECT f.code FROM tmonier_agentic_feed f JOIN sylius_channel c ON c.id = f.channel_id WHERE c.code = 'B2B' AND f.access_token IS NULL");
        $feeds = $connection->fetchOne("SELECT COUNT(*) FROM tmonier_agentic_feed f JOIN sylius_channel c ON c.id = f.channel_id WHERE c.code = 'B2B'");

        self::assertIsNumeric($feeds);
        self::assertGreaterThan(0, (int) $feeds);
        self::assertSame([], $without, 'a feed without access token is public and carries the prices');
        $client->request('GET', '/feeds/google_b2b');
        self::assertResponseStatusCodeSame(404);
    }

    /**
     * Read with SQL, not the ORM: loading product entities in the kernel of the client would change how the
     * pages it renders translate them.
     *
     * @return list<array{code: string, slug: string, taxon: string, variant: string}>
     */
    private function products(KernelBrowser $client): array
    {
        $connection = $client->getContainer()->get('doctrine.dbal.default_connection');
        self::assertInstanceOf(Connection::class, $connection);

        /** @var list<array{code: string, slug: string, taxon: string, variant: string}> $rows */
        $rows = $connection->fetchAllAssociative(
            "SELECT p.code AS code, t.slug AS slug, tt.slug AS taxon, (SELECT v.code FROM sylius_product_variant v WHERE v.product_id = p.id AND v.enabled = 1 ORDER BY v.position LIMIT 1) AS variant
             FROM sylius_product p
             JOIN sylius_product_translation t ON t.translatable_id = p.id AND t.locale = 'fr_FR'
             JOIN sylius_taxon_translation tt ON tt.translatable_id = p.main_taxon_id AND tt.locale = 'fr_FR'
             WHERE p.enabled = 1 ORDER BY p.id LIMIT 40",
        );
        self::assertNotEmpty($rows);

        return $rows;
    }

    private function login(KernelBrowser $client, string $email): void
    {
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);

        $client->loginUser($user, 'shop');
    }

    /**
     * @param array<mixed> $data
     *
     * @return list<string>
     */
    private function keysOf(array $data): array
    {
        $keys = [];
        foreach ($data as $key => $value) {
            $keys[] = (string) $key;
            if (\is_array($value)) {
                array_push($keys, ...$this->keysOf($value));
            }
        }

        return $keys;
    }
}
