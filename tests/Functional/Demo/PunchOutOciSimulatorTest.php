<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\DataFixtures\PunchoutDemoFixture;
use App\Demo\Http\InternalRequestToken;
use App\Demo\PunchOutSimulator\DemoBuyerCodes;
use App\Demo\PunchOutSimulator\OciCredentials;
use App\Demo\PunchOutSimulator\SignedState;
use App\Tests\Support\IsolatesSharedAccounts;
use App\Tests\Support\OciPostedBody;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusPunchoutCxmlPlugin\Transfer\TransferLine;

/**
 * US-053: the simulator plays an SAP OCI buyer. The pages are served (HTML), the entry goes through the real OCI
 * route of the plugin, and the cart comes back as NEW_ITEM-* fields.
 */
final class PunchOutOciSimulatorTest extends WebTestCase
{
    use DatabaseAccess;
    use EnvironmentSwitch;
    use IsolatesSharedAccounts;

    private const RETURN = '/demo/punchout-simulator/oci/return';

    private const HOOK = 'https://erp.acme.example' . self::RETURN;

    public function testTheSimulatorOffersBothProtocolsAndTheCxmlPageIsUnchanged(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/demo/punchout-simulator');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[action*="/demo/punchout-simulator/start"]');
        self::assertSelectorExists('a[href*="/demo/punchout-simulator/oci"]');
        self::assertStringNotContainsString('pas simulé', (string) $client->getResponse()->getContent());
        self::assertSame(0, $crawler->filter('input[name="HOOK_URL"]')->count());
    }

    public function testTheOciPageHasTheHookUrlFormAndNeverShowsTheSecret(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/demo/punchout-simulator/oci');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[action*="/demo/punchout-simulator/oci/start"] input[name="_token"]');
        self::assertStringContainsString('/demo/punchout-simulator/oci/return', (string) $crawler->filter('input[name="HOOK_URL"]')->attr('value'));
        $html = (string) $client->getResponse()->getContent();
        self::assertStringNotContainsString(PunchoutDemoFixture::DEMO_SECRET, $html);
        self::assertStringNotContainsString(PunchoutDemoFixture::OCI_USERNAME, $html);
    }

    public function testTheCartOfTwoLinesIsShownWithQuantitiesAndPrices(): void
    {
        $client = self::createClient();

        $client->request('POST', self::RETURN . '?state=' . rawurlencode($this->state()), OciPostedBody::of(
            new TransferLine('GANT-1', 'v1:GANT-1', 'Gants nitrile', 2, '12.50', 'EUR'),
            new TransferLine('CASQ-1', 'v1:CASQ-1', 'Casque de chantier', 3, '4.20', 'EUR'),
        ));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'Gants nitrile');
        self::assertSelectorTextContains('main', 'Casque de chantier');
        self::assertSelectorTextContains('main', '12.50');
        self::assertSelectorTextContains('main', '4.20');
        self::assertCount(2, $client->getCrawler()->filter('tbody tr'));
        self::assertSelectorNotExists('form[action*="/order"]');
    }

    public function testAnEmptyCartIsShownWithoutError(): void
    {
        $client = self::createClient();

        $client->request('POST', self::RETURN . '?state=' . rawurlencode($this->state()), ['~OkCode' => 'ADD']);

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('[role="alert"]');
        self::assertSelectorExists('[role="status"]');
        self::assertCount(0, $client->getCrawler()->filter('tbody tr'));
    }

    public function testAReplayedReturnIsRefusedAndShowsNoCart(): void
    {
        $client = self::createClient();
        $url = self::RETURN . '?state=' . rawurlencode($this->state());
        $fields = OciPostedBody::of(new TransferLine('GANT-1', 'v1:GANT-1', 'Gants nitrile', 2, '12.50', 'EUR'));

        $client->request('POST', $url, $fields);
        self::assertResponseIsSuccessful();
        $client->request('POST', $url, $fields);

        self::assertResponseStatusCodeSame(410);
        self::assertSelectorExists('[role="alert"]');
        self::assertSelectorTextNotContains('main', 'Gants nitrile');
    }

    public function testAForgedReturnShowsNoCart(): void
    {
        $client = self::createClient();

        $client->request('POST', self::RETURN . '?state=forged.value', OciPostedBody::of(new TransferLine('GANT-1', 'v1:GANT-1', 'Gants nitrile', 2, '12.50', 'EUR')));

        self::assertResponseStatusCodeSame(410);
        self::assertSelectorTextNotContains('main', 'Gants nitrile');
    }

    public function testAHookUrlOutsideTheAllowedDomainsIsRefusedBeforeAnyCallToTheShop(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/demo/punchout-simulator/oci');
        $form = $crawler->filter('form[action*="/oci/start"]')->form(['HOOK_URL' => 'https://not-allowed.example/return']);
        $sessions = $this->intValue('SELECT COUNT(*) FROM tmonier_punchout_session');

        $client->submit($form);

        // A call to the shop would have failed with 422 (nothing listens on the test server): 403 is the simulator's own refusal.
        self::assertResponseStatusCodeSame(403);
        self::assertSelectorExists('[role="alert"]');
        self::assertSame($sessions, $this->intValue('SELECT COUNT(*) FROM tmonier_punchout_session'));
    }

    public function testTheOciEntryIsLimitedPerVisitor(): void
    {
        $this->setEnvironment(['DEMO_RATE_LIMIT_PUNCHOUT_START' => '2']);
        $client = self::createClient();
        $visitor = ['HTTP_X_FORWARDED_FOR' => self::visitorIp()];

        for ($attempt = 0; $attempt < 2; ++$attempt) {
            $client->request('POST', '/demo/punchout-simulator/oci/start', server: $visitor);
            self::assertNotSame(429, $client->getResponse()->getStatusCode());
        }
        $client->request('POST', '/demo/punchout-simulator/oci/start', server: $visitor);

        self::assertResponseStatusCodeSame(429);
        self::assertTrue($client->getResponse()->headers->has('Retry-After'));
    }

    public function testTheOciReturnIsLimitedPerVisitor(): void
    {
        $this->setEnvironment(['DEMO_RATE_LIMIT_PUNCHOUT_START' => '2']);
        $client = self::createClient();
        $visitor = ['HTTP_X_FORWARDED_FOR' => self::visitorIp()];

        for ($attempt = 0; $attempt < 2; ++$attempt) {
            $client->request('POST', self::RETURN, server: $visitor);
            self::assertNotSame(429, $client->getResponse()->getStatusCode());
        }
        $client->request('POST', self::RETURN, server: $visitor);

        self::assertResponseStatusCodeSame(429);
    }

    public function testTheOciEntryOfTheShopIsReservedToTheSimulatorOnThePublicDemo(): void
    {
        $this->setEnvironment(['DEMO_PUNCHOUT_PUBLIC_ENDPOINT' => '0']);
        $client = self::createClient();

        $client->request('POST', '/punchout/oci/' . DemoBuyerCodes::OCI, $this->entryFields());

        self::assertResponseStatusCodeSame(403);
        self::assertStringContainsString('reserved to the demo simulator', (string) $client->getResponse()->getContent());
    }

    public function testTheOciEntryOfTheSimulatorPassesTheGuardWithTheInternalToken(): void
    {
        $this->setEnvironment(['DEMO_PUNCHOUT_PUBLIC_ENDPOINT' => '0']);
        $client = self::createClient();
        $token = self::getContainer()->get(InternalRequestToken::class);
        self::assertInstanceOf(InternalRequestToken::class, $token);

        $client->request('POST', '/punchout/oci/' . DemoBuyerCodes::OCI, $this->entryFields(), server: ['HTTP_X_DEMO_INTERNAL' => $token->value()]);

        self::assertResponseRedirects();
    }

    public function testAnEntryByGetIsRefusedByDefault(): void
    {
        $client = self::createClient();

        $client->request('GET', '/punchout/oci/' . DemoBuyerCodes::OCI, $this->entryFields());

        self::assertResponseStatusCodeSame(405);
    }

    public function testAWrongPasswordIsRefusedByTheShop(): void
    {
        $client = self::createClient();

        $client->request('POST', '/punchout/oci/' . DemoBuyerCodes::OCI, ['PASSWORD' => 'not-the-shared-secret'] + $this->entryFields());

        self::assertResponseStatusCodeSame(403);
    }

    /** The entry the simulator sends, the cart the shop transfers, and its display by the simulator: the real plugin in the middle. */
    public function testTheCartTransferredByTheShopComesBackToTheSimulator(): void
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $client->request('POST', '/punchout/oci/' . DemoBuyerCodes::OCI, $this->entryFields());
        self::assertResponseRedirects();
        $this->addFirstAtelierItemToTheCart($client);

        $crawler = $client->request('GET', '/fr_FR/cart/');
        $transfer = $crawler->filter('[data-test-tmonier-punchout-banner] form[action$="/punchout/transfer"]');
        self::assertCount(1, $transfer, 'The PunchOut banner offers the transfer.');
        $crawler = $client->submit($transfer->form());
        self::assertResponseIsSuccessful();
        $form = $crawler->filter('#punchout-transfer-form')->form();
        self::assertSame(self::HOOK, strtok($form->getUri(), '?'));

        $client->request('POST', $form->getUri(), $form->getPhpValues());

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, $client->getCrawler()->filter('tbody tr')->count());
        self::assertSelectorNotExists('[role="alert"]');
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

    /** @return array<string, string> */
    private function entryFields(): array
    {
        return (new OciCredentials(DemoBuyerCodes::OCI, PunchoutDemoFixture::OCI_USERNAME, PunchoutDemoFixture::DEMO_SECRET))->entryFields($this->hookUrl());
    }

    private function hookUrl(): string
    {
        return self::HOOK . '?state=' . rawurlencode($this->state());
    }

    private function state(): string
    {
        $state = self::getContainer()->get(SignedState::class);
        self::assertInstanceOf(SignedState::class, $state);

        return $state->sign(['nonce' => bin2hex(random_bytes(8)), 'oci' => true]);
    }
}
