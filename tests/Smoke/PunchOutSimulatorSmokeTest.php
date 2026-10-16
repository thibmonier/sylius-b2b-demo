<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use App\Demo\DataFixtures\PunchoutDemoFixture;
use App\Demo\DataFixtures\TradeCreditDemoFixture;
use App\Demo\PunchOutSimulator\BuyerCredentialsProvider;
use App\Demo\PunchOutSimulator\CxmlResponse;
use App\Demo\PunchOutSimulator\OrderMessageParser;
use App\Demo\PunchOutSimulator\OrderRequestBuilder;
use App\Demo\PunchOutSimulator\ReceivedCart;
use App\Demo\PunchOutSimulator\ReceivedLine;
use App\Demo\PunchOutSimulator\ResponseParser;
use App\Demo\PunchOutSimulator\SetupRequestBuilder;
use App\Demo\PunchOutSimulator\SignedState;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\NativeClock;
use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\OrderMessageWriter;
use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\SafeXmlLoader;
use Tmonier\SyliusPunchoutCxmlPlugin\Log\MessageLog;
use Tmonier\SyliusPunchoutCxmlPlugin\Log\MessageLogger;
use Tmonier\SyliusPunchoutCxmlPlugin\Transfer\TransferLine;

/**
 * US-027 smoke: the documents built by the simulator are accepted (or refused, for the error scenarios)
 * by the real PunchOut endpoint of the shop, and the cart posted back by the plugin is shown.
 * Run against a database loaded with the `demo` fixture suite.
 */
final class PunchOutSimulatorSmokeTest extends WebTestCase
{
    public function testThePageIsServedAndNeverShowsTheSharedSecret(): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/punchout-simulator');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('erp.acme.example', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString(PunchoutDemoFixture::DEMO_SECRET, (string) $client->getResponse()->getContent());
    }

    public function testTheSetupRequestOpensAShoppingSession(): void
    {
        $client = self::createClient();

        $response = $this->requestSetup($client, 'https://erp.acme.example/demo/punchout-simulator/return');

        self::assertTrue($response->isOk(), $response->message);
        self::assertNotNull($response->startToken());
    }

    public function testAWrongSecretIsRefused(): void
    {
        $client = self::createClient();

        $response = $this->requestSetup($client, 'https://erp.acme.example/return', wrongSecret: true);

        self::assertSame(401, $response->code);
    }

    public function testAReturnDomainOutsideTheWhitelistIsRefused(): void
    {
        $client = self::createClient();

        $response = $this->requestSetup($client, 'https://not-allowed.example/return');

        self::assertSame(403, $response->code);
    }

    public function testTheCartPostedBackIsShownWithTheOrderForm(): void
    {
        $client = self::createClient();
        $cookie = self::state()->sign(['nonce' => 'n']);
        $xml = (new OrderMessageWriter())->write([new TransferLine('SKU-1', 'v1:SKU-1', 'Gants nitrile', 2, '12.50', 'EUR')], '25.00', 'EUR', $cookie, 'smoke-' . bin2hex(random_bytes(6)) . '@x', 'create');
        // The shop journals the messages it sends; the simulator only believes a cart it finds there (US-040, M8).
        $logger = self::getContainer()->get(MessageLogger::class);
        self::assertInstanceOf(MessageLogger::class, $logger);
        $logger->log(MessageLog::DIRECTION_OUT, 'PunchOutOrderMessage', 'smoke-' . bin2hex(random_bytes(4)), 200, $xml);

        $client->request('POST', '/demo/punchout-simulator/return', ['cXML-base64' => base64_encode($xml)]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'Gants nitrile');
        self::assertSelectorExists('form[action*="/demo/punchout-simulator/order"] input[name="orderId"]');
    }

    public function testAnExpiredSessionShowsAMessageAndNoOrderForm(): void
    {
        $client = self::createClient();
        $xml = (new OrderMessageWriter())->write([new TransferLine('SKU-1', 'v1:SKU-1', 'Gants', 1, '1.00', 'EUR')], '1.00', 'EUR', 'expired-cookie', 'p@x', 'create');

        $client->request('POST', '/demo/punchout-simulator/return', ['cXML-base64' => base64_encode($xml)]);

        self::assertResponseStatusCodeSame(410);
        self::assertSelectorExists('[role="alert"]');
        self::assertSelectorNotExists('form[action*="/order"]');
    }

    public function testTheOrderRequestOfTheSimulatorReachesTheOrderHandlerOfTheShop(): void
    {
        $client = self::createClient();
        $cart = new ReceivedCart('c', 'EUR', '1.00', [new ReceivedLine('UNKNOWN-SKU', 'v1:UNKNOWN-SKU', 'Inconnu', 1, '1.00', 'EUR', 'EA')]);
        $xml = new OrderRequestBuilder(new NativeClock())->build(self::credentials()->provide(), $cart, 'PO-SMOKE-1');

        $client->request('POST', '/punchout/cxml', server: ['CONTENT_TYPE' => 'text/xml'], content: $xml);
        $response = new ResponseParser(new SafeXmlLoader())->parse((string) $client->getResponse()->getContent());

        // Authenticated and parsed: refused only because the item is not in the catalog (no order created).
        self::assertNotSame(401, $response->code);
        self::assertStringContainsString('Items not available', $response->message);
    }

    /**
     * End to end: the cart the shop transfers (its own prices and stock) becomes an order through the
     * OrderRequest of the simulator, paid on account like a shop order.
     */
    public function testTheTransferredCartBecomesAnOrderThroughTheOrderRequest(): void
    {
        $client = self::createClient();
        $setup = $this->requestSetup($client, 'https://erp.acme.example/demo/punchout-simulator/return');
        self::assertNotNull($setup->startToken(), $setup->message);

        $client->request('GET', '/punchout/start/' . $setup->startToken());
        self::assertResponseRedirects();
        $this->addFirstAtelierItemToTheCart($client);

        $crawler = $client->request('GET', '/fr_FR/cart/');
        $transfer = $crawler->filter('[data-test-tmonier-punchout-banner] form[action$="/punchout/transfer"]');
        self::assertCount(1, $transfer, 'The PunchOut banner offers the transfer.');
        $crawler = $client->submit($transfer->form());
        self::assertResponseIsSuccessful();
        // What the real plugin posts back is accepted by the simulator (the message is in the journal of the shop).
        $posted = (string) $crawler->filter('input[name="cXML-base64"]')->attr('value');
        $client->request('POST', '/demo/punchout-simulator/return', ['cXML-base64' => $posted]);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[action*="/demo/punchout-simulator/order"] input[name="orderId"]');
        $parser = self::getContainer()->get(OrderMessageParser::class);
        self::assertInstanceOf(OrderMessageParser::class, $parser);
        $cart = $parser->parseBase64((string) $crawler->filter('input[name="cXML-base64"]')->attr('value'));
        self::assertCount(1, $cart->lines, 'The transferred cart holds the item added in the shop.');

        $orderId = 'PO-E2E-' . strtoupper(bin2hex(random_bytes(3)));
        $xml = new OrderRequestBuilder(new NativeClock())->build(self::credentials()->provide(), $cart, $orderId);
        $client->request('POST', '/punchout/cxml', server: ['CONTENT_TYPE' => 'text/xml'], content: $xml);
        $response = new ResponseParser(new SafeXmlLoader())->parse((string) $client->getResponse()->getContent());
        self::assertTrue($response->isOk(), $response->message);

        $order = $this->orderOf($orderId);
        self::assertSame('completed', $order->getCheckoutState());
        self::assertSame('claire@acme.example', $order->getCustomer()?->getEmailCanonical());
        self::assertSame($cart->lines[0]->quantity, $order->getTotalQuantity());
        self::assertSame(TradeCreditDemoFixture::ON_ACCOUNT_PAYMENT_METHOD, $order->getLastPayment()?->getMethod()?->getCode());
    }

    private function addFirstAtelierItemToTheCart(KernelBrowser $client): void
    {
        $crawler = $client->request('GET', '/fr_FR/account/company-wishlists');
        $crawler = $client->request('GET', (string) $crawler->filter('[data-test-company-wishlist-link]')->attr('href'));
        $form = $crawler->filter('#company-wishlist-cart');
        $client->request('POST', (string) $form->attr('action'), [
            '_csrf_token' => (string) $form->filter('input[name="_csrf_token"]')->attr('value'),
            'items' => [(string) $form->filter('input[name="items[]"]')->first()->attr('value')],
        ]);
        self::assertResponseRedirects();
    }

    private function orderOf(string $purchaseOrder): OrderInterface
    {
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $orders = $entityManager->createQueryBuilder()
            ->select('o')->from(OrderInterface::class, 'o')
            ->where('o.notes LIKE :notes')->setParameter('notes', 'PunchOut purchase order ' . $purchaseOrder . '%')
            ->getQuery()->getResult();
        self::assertIsArray($orders);
        self::assertCount(1, $orders, 'One order is created for the purchase order.');
        self::assertInstanceOf(OrderInterface::class, $orders[0]);

        return $orders[0];
    }

    private static function state(): SignedState
    {
        $secret = self::getContainer()->getParameter('kernel.secret');
        \assert(is_string($secret));

        return new SignedState($secret, new NativeClock());
    }

    private static function credentials(): BuyerCredentialsProvider
    {
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);

        return new BuyerCredentialsProvider($entityManager);
    }

    private function requestSetup(KernelBrowser $client, string $returnUrl, bool $wrongSecret = false): CxmlResponse
    {
        $xml = new SetupRequestBuilder(new NativeClock())->build(
            self::credentials()->provide($wrongSecret),
            self::state()->sign(['nonce' => bin2hex(random_bytes(4))]),
            $returnUrl,
        );

        $client->request('POST', '/punchout/cxml', server: ['CONTENT_TYPE' => 'text/xml'], content: $xml);

        return new ResponseParser(new SafeXmlLoader())->parse((string) $client->getResponse()->getContent());
    }
}
