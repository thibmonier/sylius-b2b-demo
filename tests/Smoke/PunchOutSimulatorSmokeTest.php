<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use App\Demo\DataFixtures\PunchoutDemoFixture;
use App\Demo\PunchOutSimulator\BuyerCredentialsProvider;
use App\Demo\PunchOutSimulator\CxmlResponse;
use App\Demo\PunchOutSimulator\OrderRequestBuilder;
use App\Demo\PunchOutSimulator\ReceivedCart;
use App\Demo\PunchOutSimulator\ReceivedLine;
use App\Demo\PunchOutSimulator\ResponseParser;
use App\Demo\PunchOutSimulator\SetupRequestBuilder;
use App\Demo\PunchOutSimulator\SignedState;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\NativeClock;
use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\OrderMessageWriter;
use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\SafeXmlLoader;
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
        $xml = (new OrderMessageWriter())->write([new TransferLine('SKU-1', 'v1:SKU-1', 'Gants nitrile', 2, '12.50', 'EUR')], '25.00', 'EUR', $cookie, 'p@x', 'create');

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
