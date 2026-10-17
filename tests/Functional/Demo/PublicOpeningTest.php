<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Channel\DemoChannelContext;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * US-050: the two modes of the demo seen from the outside. Private: the HTTP gate is on (401, MCP guide warning, the
 * preflight asks for the credentials of the gate). Public: the gate is off (200, no warning), the visitor cannot leave
 * personal data (registration, guest checkout, guest quote) and the demo asks search engines to stay away. The
 * configuration is set by each test (never shared); the rendered answers are what is asserted.
 */
final class PublicOpeningTest extends WebTestCase
{
    use EnvironmentSwitch;

    private const USER = 'visitor';

    private const PASSWORD = 'correct horse battery';

    public function testPrivateModeRefusesAnonymousVisitorsAndWarnsInTheMcpGuide(): void
    {
        $this->privateMode();
        $client = self::createClient();

        $client->request('GET', '/fr_FR/');
        self::assertResponseStatusCodeSame(401);

        $client->request('GET', '/en_US/mcp', server: $this->credentials());
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-mcp-private-note]');
    }

    public function testPrivateModeKeepsTheHealthchecksOpen(): void
    {
        $this->privateMode();
        $client = self::createClient();

        $client->request('GET', '/healthz');

        self::assertNotSame(401, $client->getResponse()->getStatusCode());
    }

    public function testPublicModeServesAnonymousVisitorsWithoutAnyPrivateWarning(): void
    {
        $this->publicMode();
        $client = self::createClient();

        $client->request('GET', '/fr_FR/');
        self::assertResponseIsSuccessful();

        foreach (['fr_FR', 'en_US'] as $locale) {
            $client->request('GET', '/' . $locale . '/mcp');
            self::assertResponseIsSuccessful();
            self::assertSelectorNotExists('[data-mcp-private-note]');
            self::assertStringNotContainsString('démo est privée', (string) $client->getResponse()->getContent());
            self::assertStringNotContainsString('demo is private', (string) $client->getResponse()->getContent());
        }
    }

    public function testPublicModeRefusesRegistrations(): void
    {
        $this->publicMode();
        $client = self::createClient();

        $client->request('GET', '/fr_FR/register');

        self::assertResponseStatusCodeSame(403);
        self::assertSelectorNotExists('form[name="sylius_shop_customer_registration"]');
    }

    public function testPublicModeRefusesTheGuestCheckoutButNotTheSignedInCustomer(): void
    {
        $this->publicMode();
        $client = $this->consumerClient();

        $client->request('GET', '/fr_FR/checkout/address');
        self::assertResponseStatusCodeSame(403);
        self::assertStringContainsString('Les inscriptions sont fermées', (string) $client->getResponse()->getContent());

        $client->request('PUT', '/fr_FR/checkout/address');
        self::assertResponseStatusCodeSame(403);

        $this->login($client, 'claire@acme.example');
        $client->request('GET', '/fr_FR/checkout/address');
        self::assertStringNotContainsString('Les inscriptions sont fermées', (string) $client->getResponse()->getContent());
    }

    public function testTheGuestCheckoutStaysOpenWhileRegistrationsAre(): void
    {
        $this->privateMode();
        $this->setEnvironment(['DEMO_REGISTRATION_OPEN' => '1']);
        $client = $this->consumerClient();

        $client->request('GET', '/fr_FR/checkout/address', server: $this->credentials());

        self::assertStringNotContainsString('Les inscriptions sont fermées', (string) $client->getResponse()->getContent());
    }

    public function testPublicModeSendsAGuestToTheSignInInsteadOfTheQuoteForm(): void
    {
        $this->publicMode();
        $client = $this->consumerClient();

        $client->request('GET', '/fr_FR/quote-request');

        self::assertResponseRedirects();
        self::assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }

    public function testAGuestMayAskForAQuoteWhileRegistrationsAreOpen(): void
    {
        $this->privateMode();
        $this->setEnvironment(['DEMO_REGISTRATION_OPEN' => '1']);
        $client = $this->consumerClient();

        $client->request('GET', '/fr_FR/quote-request', server: $this->credentials());

        self::assertFalse($client->getResponse()->isRedirect(), 'the quote form is served to a guest');
    }

    public function testPublicModeAsksSearchEnginesToStayAway(): void
    {
        $this->publicMode();
        $client = self::createClient();

        $client->request('GET', '/robots.txt');

        self::assertResponseIsSuccessful();
        self::assertStringStartsWith('text/plain', (string) $client->getResponse()->headers->get('Content-Type'));
        self::assertMatchesRegularExpression('/^User-agent: \*\R+Disallow: \/\s*$/m', (string) $client->getResponse()->getContent());

        $client->request('GET', '/fr_FR/');
        self::assertResponseHeaderSame('X-Robots-Tag', 'noindex, nofollow');
    }

    public function testPrivateModeAlsoCarriesTheNoindexHeader(): void
    {
        $this->privateMode();
        $client = self::createClient();

        $client->request('GET', '/fr_FR/');

        self::assertResponseStatusCodeSame(401);
        self::assertResponseHeaderSame('X-Robots-Tag', 'noindex, nofollow');
    }

    public function testRobotsTxtFollowsTheGateInPrivateMode(): void
    {
        $this->privateMode();
        $client = self::createClient();

        $client->request('GET', '/robots.txt');
        self::assertResponseStatusCodeSame(401);

        $client->request('GET', '/robots.txt', server: $this->credentials());
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Disallow: /', (string) $client->getResponse()->getContent());
    }

    public function testPreflightOfThePrivateModeAsksForTheCredentialsOfTheGate(): void
    {
        $this->privateMode();
        $this->setEnvironment(['DEMO_BASIC_AUTH_USER' => '', 'DEMO_BASIC_AUTH_PASSWORD_HASH' => '']);

        [$status, $output] = $this->preflight();

        self::assertSame(1, $status);
        self::assertStringContainsString('DEMO_BASIC_AUTH_USER', $output);
    }

    public function testPreflightOfThePrivateModeAcceptsOpenRegistrations(): void
    {
        $this->privateMode();
        $this->setEnvironment(['DEMO_REGISTRATION_OPEN' => '1']);

        [$status] = $this->preflight();

        self::assertSame(0, $status);
    }

    public function testPreflightOfThePublicModeNeedsNoGateCredentials(): void
    {
        $this->publicMode();
        $this->setEnvironment(['DEMO_BASIC_AUTH_USER' => '', 'DEMO_BASIC_AUTH_PASSWORD_HASH' => '']);

        [$status, $output] = $this->preflight();

        self::assertSame(0, $status, $output);
    }

    public function testPreflightRefusesAnOpenGateTogetherWithOpenRegistrations(): void
    {
        $this->publicMode();
        $this->setEnvironment(['DEMO_REGISTRATION_OPEN' => '1']);

        [$status, $output] = $this->preflight();

        self::assertSame(1, $status);
        self::assertStringContainsString('DEMO_REGISTRATION_OPEN', $output);
    }

    public function testPreflightRefusesAnOpenGateTogetherWithAPublicPunchOutEndpoint(): void
    {
        $this->publicMode();
        $this->setEnvironment(['DEMO_PUNCHOUT_PUBLIC_ENDPOINT' => '1']);

        [$status, $output] = $this->preflight();

        self::assertSame(1, $status);
        self::assertStringContainsString('DEMO_PUNCHOUT_PUBLIC_ENDPOINT', $output);
    }

    /** The guest paths live on the consumer channel (the B2B channel already sends a visitor to the sign-in). */
    private function consumerClient(): KernelBrowser
    {
        $client = self::createClient();
        $client->getCookieJar()->set(new Cookie(DemoChannelContext::COOKIE, DemoChannelContext::B2C));

        return $client;
    }

    private function privateMode(): void
    {
        $this->setEnvironment([
            'DEMO_BASIC_AUTH' => '1',
            'DEMO_BASIC_AUTH_USER' => self::USER,
            'DEMO_BASIC_AUTH_PASSWORD_HASH' => self::gateHash(self::PASSWORD),
        ]);
    }

    private function publicMode(): void
    {
        $this->setEnvironment(['DEMO_BASIC_AUTH' => '0', 'DEMO_REGISTRATION_OPEN' => '0', 'DEMO_PUNCHOUT_PUBLIC_ENDPOINT' => '0']);
    }

    /** @return array<string, string> */
    private function credentials(): array
    {
        return ['PHP_AUTH_USER' => self::USER, 'PHP_AUTH_PW' => self::PASSWORD];
    }

    /** @return array{int, string} exit status and display of app:demo:preflight under the current environment */
    private function preflight(): array
    {
        $application = new Application(self::bootKernel());
        $command = $application->find('app:demo:preflight');
        $tester = new CommandTester($command);

        return [$tester->execute([]), $tester->getDisplay()];
    }

    private function login(KernelBrowser $client, string $email): void
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(\Doctrine\ORM\EntityManagerInterface::class, $em);
        $customer = $em->getRepository(\Sylius\Component\Core\Model\CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(\Sylius\Component\Core\Model\CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);

        $client->loginUser($user, 'shop');
    }
}
