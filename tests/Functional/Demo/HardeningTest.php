<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Http\InternalRequestToken;
use App\Demo\PunchOutSimulator\BuyerCredentialsProvider;
use App\Demo\PunchOutSimulator\SetupRequestBuilder;
use App\Demo\PunchOutSimulator\SignedState;
use App\Tests\Support\Await;
use App\Tests\Support\RestoresDemoCarts;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\NativeClock;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;

/**
 * US-036: the hardening of the public demo, asserted on what is rendered (status codes, headers, HTML),
 * one test per finding carried over from the sprint 2, 4 and 5 reviews. Run against a database loaded
 * with the `demo` fixture suite.
 */
final class HardeningTest extends WebTestCase
{
    use EnvironmentSwitch;
    use RestoresDemoCarts;

    private const PASSWORD = 'Demo1234!';

    /** @var resource|null */
    private $endpoint;

    private string $captureFile = '';

    protected function tearDown(): void
    {
        if (\is_resource($this->endpoint)) {
            proc_terminate($this->endpoint);
            proc_close($this->endpoint);
            $this->endpoint = null;
        }
        if ('' !== $this->captureFile && is_file($this->captureFile)) {
            unlink($this->captureFile);
        }
        parent::tearDown();
    }

    // ---- S2-R1: rate limiting per visitor IP (T-036-01, T-036-02)

    public function testTheShopLoginIsLimitedPerVisitorAndAnotherVisitorIsNotBlocked(): void
    {
        $this->setEnvironment(['DEMO_RATE_LIMIT_LOGIN' => '3']);
        $client = self::createClient();
        $visitor = self::visitorIp();

        for ($attempt = 1; $attempt <= 3; ++$attempt) {
            $this->postLogin($client, '/fr_FR/login-check', $visitor);
            self::assertNotSame(429, $client->getResponse()->getStatusCode(), 'Attempt ' . $attempt);
        }
        $this->postLogin($client, '/fr_FR/login-check', $visitor);

        self::assertResponseStatusCodeSame(429);
        self::assertGreaterThan(0, (int) $client->getResponse()->headers->get('Retry-After'));

        $this->postLogin($client, '/fr_FR/login-check', self::visitorIp());
        self::assertNotSame(429, $client->getResponse()->getStatusCode(), 'Another IP has its own counter.');
    }

    public function testTheAdminLoginIsLimitedPerVisitor(): void
    {
        $this->setEnvironment(['DEMO_RATE_LIMIT_LOGIN' => '2']);
        $client = self::createClient();
        $visitor = self::visitorIp();

        $this->postLogin($client, '/admin/login-check', $visitor);
        $this->postLogin($client, '/admin/login-check', $visitor);
        self::assertNotSame(429, $client->getResponse()->getStatusCode());
        $this->postLogin($client, '/admin/login-check', $visitor);

        self::assertResponseStatusCodeSame(429);
    }

    public function testTheDemoAccountsPageIsLimitedPerVisitor(): void
    {
        $this->setEnvironment(['DEMO_RATE_LIMIT_PUBLIC' => '2']);
        $client = self::createClient();
        $visitor = ['HTTP_X_FORWARDED_FOR' => self::visitorIp()];

        $client->request('GET', '/demo/accounts', server: $visitor);
        $client->request('GET', '/demo/accounts', server: $visitor);
        self::assertResponseIsSuccessful();
        $client->request('GET', '/demo/accounts', server: $visitor);

        self::assertResponseStatusCodeSame(429);
    }

    public function testTheRegistrationIsLimitedPerVisitorButItsFormStaysReadable(): void
    {
        $this->setEnvironment(['DEMO_RATE_LIMIT_FORMS' => '2']);
        $client = self::createClient();
        $visitor = ['HTTP_X_FORWARDED_FOR' => self::visitorIp()];

        $client->request('POST', '/fr_FR/register', server: $visitor);
        $client->request('POST', '/fr_FR/register', server: $visitor);
        self::assertNotSame(429, $client->getResponse()->getStatusCode());
        $client->request('POST', '/fr_FR/register', server: $visitor);
        self::assertResponseStatusCodeSame(429);

        $client->request('GET', '/fr_FR/register', server: $visitor);
        self::assertResponseIsSuccessful();
    }

    // ---- API, sign-up and MCP endpoints are limited per visitor too

    #[DataProvider('provideApiEndpoints')]
    public function testTheApiEndpointsAreLimitedPerVisitor(string $path, string $limitVariable): void
    {
        $this->setEnvironment([$limitVariable => '2']);
        $client = self::createClient();
        $visitor = self::visitorIp();
        $post = static fn (string $ip) => $client->request('POST', $path, server: ['HTTP_X_FORWARDED_FOR' => $ip, 'CONTENT_TYPE' => 'application/json'], content: '{}');

        $post($visitor);
        self::assertNotSame(429, $client->getResponse()->getStatusCode(), 'First call is served.');
        $post($visitor);
        self::assertNotSame(429, $client->getResponse()->getStatusCode(), 'Second call is served.');
        $post($visitor);
        self::assertResponseStatusCodeSame(429);
        self::assertGreaterThan(0, (int) $client->getResponse()->headers->get('Retry-After'));

        $post(self::visitorIp());
        self::assertNotSame(429, $client->getResponse()->getStatusCode(), 'Another visitor is not blocked.');
    }

    /** @return iterable<string, array{string, string}> */
    public static function provideApiEndpoints(): iterable
    {
        yield 'shop token' => ['/api/v2/shop/customers/token', 'DEMO_RATE_LIMIT_API_AUTH'];
        yield 'admin token' => ['/api/v2/admin/administrators/token', 'DEMO_RATE_LIMIT_API_AUTH'];
        yield 'API sign-up' => ['/api/v2/shop/customers', 'DEMO_RATE_LIMIT_API_WRITE'];
        yield 'API cart' => ['/api/v2/shop/orders', 'DEMO_RATE_LIMIT_API_WRITE'];
        yield 'MCP OAuth registration' => ['/mcp-oauth/register', 'DEMO_RATE_LIMIT_API_WRITE'];
        yield 'MCP' => ['/_mcp', 'DEMO_RATE_LIMIT_MCP'];
    }

    public function testReadingTheApiIsNotLimited(): void
    {
        $this->setEnvironment(['DEMO_RATE_LIMIT_API_WRITE' => '1']);
        $client = self::createClient();
        $visitor = ['HTTP_X_FORWARDED_FOR' => self::visitorIp()];

        for ($call = 0; $call < 3; ++$call) {
            $client->request('GET', '/api/v2/shop/orders/unknown-token', server: $visitor);
            self::assertNotSame(429, $client->getResponse()->getStatusCode());
        }
    }

    // ---- fail-closed settings (the demo refuses to serve with a weak secret or open hosts)

    #[DataProvider('provideWeakSettings')]
    public function testAShortSecretOrOpenHostsMakeTheDemoRefuseToServe(string $name, string $value): void
    {
        $this->setEnvironment([$name => $value]);
        $client = self::createClient();

        $client->request('GET', '/health');

        self::assertResponseStatusCodeSame(503);
        self::assertSame('misconfigured', $client->getResponse()->getContent());
    }

    /** @return iterable<string, array{string, string}> */
    public static function provideWeakSettings(): iterable
    {
        yield 'short secret' => ['APP_SECRET', 'too-short'];
        yield 'open hosts' => ['TRUSTED_HOSTS', '^.*$'];
    }

    // ---- S2-R1: nothing leaves the demo (T-036-06)

    #[DataProvider('provideExternalMailers')]
    public function testAnExternalMailerPreventsTheDemoFromServing(string $dsn): void
    {
        $this->setEnvironment(['MAILER_DSN' => $dsn]);
        $client = self::createClient();

        $client->request('GET', '/fr_FR/');
        self::assertResponseStatusCodeSame(503);

        $client->request('GET', '/health');
        self::assertResponseStatusCodeSame(503);
        self::assertSame('misconfigured', $client->getResponse()->getContent());
    }

    /** @return iterable<string, array{string}> */
    public static function provideExternalMailers(): iterable
    {
        yield 'external SMTP' => ['smtp://smtp.provider.example:587'];
        yield 'sendmail' => ['sendmail://default'];
        yield 'API transport' => ['ses+api://KEY:SECRET@default'];
        yield 'garbage' => ['not a dsn'];
    }

    #[DataProvider('provideInternalMailers')]
    public function testTheInternalMailpitAndTheSinkAreAccepted(string $dsn): void
    {
        $this->setEnvironment(['MAILER_DSN' => $dsn]);
        $client = self::createClient();

        $client->request('GET', '/fr_FR/');

        self::assertResponseIsSuccessful();
    }

    /** @return iterable<string, array{string}> */
    public static function provideInternalMailers(): iterable
    {
        yield 'compose Mailpit' => ['smtp://mailpit:1025'];
        yield 'Railway private network' => ['smtp://mailpit.railway.internal:1025'];
        yield 'null sink' => ['null://null'];
    }

    // ---- S4-R2: "no real data" notice (T-036-08)

    #[DataProvider('provideLocales')]
    public function testTheRegistrationPageWarnsAgainstRealData(string $locale, string $expected): void
    {
        $client = self::createClient();

        $client->request('GET', '/' . $locale . '/register');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('[data-demo-no-real-data]', $expected);
    }

    #[DataProvider('provideLocales')]
    public function testTheAddressPagesWarnAgainstRealData(string $locale, string $expected): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $client->request('GET', '/' . $locale . '/account/address-book/add');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('[data-demo-no-real-data]', $expected);

        $this->fillCart($client);
        $client->request('GET', '/' . $locale . '/checkout/address');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('[data-demo-no-real-data]', $expected);
    }

    /** @return iterable<string, array{string, string}> */
    public static function provideLocales(): iterable
    {
        yield 'French' => ['fr_FR', 'aucune donnée réelle'];
        yield 'English' => ['en_US', 'Do not enter any real data'];
    }

    // ---- S4-R4: no anonymous session from the banner (T-036-10)

    public function testAnAnonymousVisitorGetsNoSessionCookieFromTheBanner(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/fr_FR/');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $client->getResponse()->headers->getCookies(), 'No Set-Cookie at all for an anonymous page view.');
        self::assertGreaterThan(0, $crawler->filter('[data-demo-banner] form [name="_csrf_shop_security_token"]')->count(), 'The banner still offers the one-click logins.');
        self::assertContains('Cookie', $client->getResponse()->getVary());
    }

    public function testTheOneClickLoginOfTheBannerWorksWithTheStatelessToken(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/fr_FR/');

        $form = $crawler->filter('[data-demo-banner] form')->reduce(
            static fn (Crawler $node): bool => str_contains((string) $node->html(), 'claire@acme.example'),
        );
        self::assertCount(1, $form);
        $client->request('POST', (string) $form->attr('action'), [
            '_username' => 'claire@acme.example',
            '_password' => self::PASSWORD,
            '_csrf_shop_security_token' => (string) $form->filter('[name="_csrf_shop_security_token"]')->attr('value'),
        ], server: ['HTTP_ORIGIN' => 'http://localhost']);
        self::assertResponseRedirects();

        $client->request('GET', '/fr_FR/account/dashboard');
        self::assertResponseIsSuccessful();
    }

    public function testALoginPostedFromAnotherOriginIsRefused(): void
    {
        $client = self::createClient();

        $client->request('POST', '/fr_FR/login-check', [
            '_username' => 'claire@acme.example',
            '_password' => self::PASSWORD,
            '_csrf_shop_security_token' => 'csrf-token',
        ], server: ['HTTP_ORIGIN' => 'https://evil.example']);

        $client->request('GET', '/fr_FR/account/dashboard');
        self::assertResponseRedirects();
        self::assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'), 'The forged login did not sign anybody in.');
    }

    public function testTogglingTheBadgesOpensNoSession(): void
    {
        $client = self::createClient();

        $client->request('GET', '/demo/badges/off');

        self::assertResponseRedirects();
        $names = array_map(static fn ($cookie): string => $cookie->getName(), $client->getResponse()->headers->getCookies());
        self::assertSame(['demo_badges'], $names);
    }

    // ---- S4-R5: legal pages follow the channel locales (T-036-11)

    #[DataProvider('provideLegalPaths')]
    public function testALegalPageIsServedForTheChannelLocaleOnly(string $path): void
    {
        $client = self::createClient();

        $client->request('GET', '/fr_FR/' . $path);
        self::assertResponseIsSuccessful();

        $client->request('GET', '/xx_XX/' . $path);
        self::assertResponseStatusCodeSame(404);

        $client->request('GET', '/de_DE/' . $path);
        self::assertResponseStatusCodeSame(404);
    }

    /** @return iterable<string, array{string}> */
    public static function provideLegalPaths(): iterable
    {
        yield 'legal notice' => ['mentions-legales'];
        yield 'about' => ['a-propos'];
    }

    // ---- S5-R1: the PunchOut endpoint and the simulator (T-036-03)

    public function testTheSharedSecretIsOfNoUseOnTheEndpointToAPublicClient(): void
    {
        $this->setEnvironment(['DEMO_PUNCHOUT_PUBLIC_ENDPOINT' => '0']);
        $client = self::createClient();
        $setup = $this->setupRequest();

        $client->request('POST', '/punchout/cxml', server: ['CONTENT_TYPE' => 'text/xml'], content: $setup);
        self::assertResponseStatusCodeSame(403);
        self::assertStringNotContainsString('PunchOutSetupResponse', (string) $client->getResponse()->getContent());

        $client->request('POST', '/punchout/cxml', server: ['CONTENT_TYPE' => 'text/xml', 'HTTP_X_FORWARDED_FOR' => '198.51.100.5', 'HTTP_X_DEMO_INTERNAL' => 'forged'], content: $setup);
        self::assertResponseStatusCodeSame(403);
    }

    public function testTheSimulatorStillReachesTheEndpointWithItsInternalToken(): void
    {
        $this->setEnvironment(['DEMO_PUNCHOUT_PUBLIC_ENDPOINT' => '0']);
        $client = self::createClient();
        $token = self::getContainer()->get(InternalRequestToken::class);
        self::assertInstanceOf(InternalRequestToken::class, $token);

        $client->request('POST', '/punchout/cxml', server: ['CONTENT_TYPE' => 'text/xml', 'HTTP_X_DEMO_INTERNAL' => $token->value()], content: $this->setupRequest());

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('PunchOutSetupResponse', (string) $client->getResponse()->getContent());
    }

    public function testASimulatorSessionCreatesOneOrderAndAReplayIsRefused(): void
    {
        $this->startFakeEndpoint();
        $this->setEnvironment(['DEMO_PUNCHOUT_DAILY_ORDERS' => '100000']);
        $client = self::createClient();
        $state = $this->orderState();
        $visitor = ['HTTP_X_FORWARDED_FOR' => self::visitorIp()];
        $fields = ['_token' => $this->orderToken($client), 'state' => $state, 'orderId' => 'PO-HARDEN-1'];

        $client->request('POST', '/demo/punchout-simulator/order', $fields, server: $visitor);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-demo-sent], .success, main');
        self::assertCount(1, $this->captured());

        $client->request('POST', '/demo/punchout-simulator/order', $fields, server: $visitor);
        self::assertResponseStatusCodeSame(410);
        self::assertStringContainsString('déjà été envoyée', (string) $client->getResponse()->getContent());
        self::assertCount(1, $this->captured(), 'The replay never reaches the shop.');
    }

    /** US-082: the order form of the simulator carries a CSRF token; without a valid one nothing is sent. */
    public function testASimulatorOrderWithoutAValidTokenIsRefusedAndSendsNothing(): void
    {
        $this->startFakeEndpoint();
        $this->setEnvironment(['DEMO_PUNCHOUT_DAILY_ORDERS' => '100000']);
        $client = self::createClient();
        $state = $this->orderState();

        foreach ([[], ['_token' => 'forged']] as $token) {
            $client->request('POST', '/demo/punchout-simulator/order', $token + ['state' => $state, 'orderId' => 'PO-HARDEN-6'], server: ['HTTP_X_FORWARDED_FOR' => self::visitorIp()]);

            self::assertResponseStatusCodeSame(422);
            self::assertStringContainsString('Formulaire expiré', (string) $client->getResponse()->getContent());
        }
        self::assertSame([], $this->captured());
    }

    public function testTheDailyCapOfAnonymousOrdersRefusesWithADemoMessageAndCreatesNothing(): void
    {
        $this->startFakeEndpoint();
        $this->setEnvironment(['DEMO_PUNCHOUT_DAILY_ORDERS' => '0']);
        $client = self::createClient();

        $client->request('POST', '/demo/punchout-simulator/order', ['_token' => $this->orderToken($client), 'state' => $this->orderState(), 'orderId' => 'PO-HARDEN-2'], server: ['HTTP_X_FORWARDED_FOR' => self::visitorIp()]);

        self::assertResponseStatusCodeSame(429);
        self::assertStringContainsString('plafond quotidien', (string) $client->getResponse()->getContent());
        self::assertSame([], $this->captured());
    }

    public function testASimulatorOrderFromAForgedSessionIsRefused(): void
    {
        $this->startFakeEndpoint();
        $client = self::createClient();
        $unsigned = self::getContainer()->get(SignedState::class);
        self::assertInstanceOf(SignedState::class, $unsigned);
        $state = $unsigned->sign(['cookie' => 'not-a-signed-cookie', 'currency' => 'EUR', 'total' => '1.00', 'lines' => [$this->line()]]);

        $client->request('POST', '/demo/punchout-simulator/order', ['_token' => $this->orderToken($client), 'state' => $state, 'orderId' => 'PO-HARDEN-3']);

        self::assertResponseStatusCodeSame(410);
        self::assertSame([], $this->captured());
    }

    // ---- S5-R3: the plugin sees the visitor, not the loopback (T-036-04)

    public function testTheSimulatorCallsThePluginWithTheVisitorAddress(): void
    {
        $this->startFakeEndpoint();
        $this->setEnvironment(['DEMO_PUNCHOUT_DAILY_ORDERS' => '100000']);
        $client = self::createClient();
        $first = self::visitorIp();
        $second = self::visitorIp();

        $client->request('POST', '/demo/punchout-simulator/order', ['_token' => $this->orderToken($client), 'state' => $this->orderState(), 'orderId' => 'PO-HARDEN-4'], server: ['HTTP_X_FORWARDED_FOR' => $first]);
        $client->request('POST', '/demo/punchout-simulator/order', ['_token' => $this->orderToken($client), 'state' => $this->orderState(), 'orderId' => 'PO-HARDEN-5'], server: ['HTTP_X_FORWARDED_FOR' => $second]);

        $calls = $this->captured();
        self::assertCount(2, $calls);
        self::assertSame([$first, $second], array_map(static fn (array $call): mixed => $call['X-FORWARDED-FOR'] ?? null, $calls), 'Each visitor has its own identity.');
        foreach ($calls as $call) {
            self::assertNotSame('127.0.0.1', $call['X-FORWARDED-FOR']);
            self::assertNotSame('', $call['X-DEMO-INTERNAL'] ?? '');
        }
    }

    // ---- S5-R4: the demo mailbox (T-036-05) is covered on its rendered output in
    // tests/Unit/Demo/Mail/DemoMailsControllerTest (a mail to a visitor address is neither listed nor readable).

    // ---- T-036-07: security headers

    #[DataProvider('providePages')]
    public function testEveryKindOfResponseCarriesTheSecurityHeaders(string $method, string $path, int $status): void
    {
        $client = self::createClient();

        $client->request($method, $path);

        self::assertResponseStatusCodeSame($status);
        $headers = $client->getResponse()->headers;
        self::assertSame('nosniff', $headers->get('X-Content-Type-Options'), $path);
        self::assertSame('DENY', $headers->get('X-Frame-Options'), $path);
        self::assertSame('strict-origin-when-cross-origin', $headers->get('Referrer-Policy'), $path);
        self::assertStringContainsString('camera=()', (string) $headers->get('Permissions-Policy'), $path);
        self::assertSame('same-origin', $headers->get('Cross-Origin-Opener-Policy'), $path);
        self::assertSame('same-origin', $headers->get('Cross-Origin-Resource-Policy'), $path);
        self::assertStringContainsString("frame-ancestors 'none'", (string) $headers->get('Content-Security-Policy'), $path);
        self::assertStringContainsString("object-src 'none'", (string) $headers->get('Content-Security-Policy'), $path);
        self::assertFalse($headers->has('Strict-Transport-Security'), 'No HSTS over plain HTTP.');
    }

    /** @return iterable<string, array{string, string, int}> */
    public static function providePages(): iterable
    {
        yield 'shop' => ['GET', '/fr_FR/', 200];
        yield 'admin login' => ['GET', '/admin/login', 200];
        yield 'demo page' => ['GET', '/demo/accounts', 200];
        yield 'redirect' => ['GET', '/demo/badges/off', 302];
        yield '404' => ['GET', '/fr_FR/no-such-page', 404];
        yield '405' => ['POST', '/health', 405];
        yield 'admin denied' => ['GET', '/admin/', 302];
    }

    public function testHstsIsSentOnlyWhenTheEffectiveRequestIsHttps(): void
    {
        $client = self::createClient();

        $client->request('GET', '/fr_FR/', server: ['HTTP_X_FORWARDED_PROTO' => 'https']);

        self::assertStringContainsString('max-age=31536000', (string) $client->getResponse()->headers->get('Strict-Transport-Security'));
    }

    public function testAnErrorPageCarriesExactlyOneHstsHeader(): void
    {
        $client = self::createClient();

        $client->request('GET', '/fr_FR/no-such-page', server: ['HTTP_X_FORWARDED_PROTO' => 'https']);

        self::assertResponseStatusCodeSame(404);
        self::assertCount(1, $client->getResponse()->headers->all('Strict-Transport-Security'));
    }

    #[DataProvider('provideKeyPages')]
    public function testKeyPagesLoadNothingFromAnotherOrigin(string $path, bool $signedIn): void
    {
        $client = self::createClient();
        if ($signedIn) {
            $this->login($client, 'claire@acme.example');
            $this->fillCart($client);
        }

        $crawler = $client->request('GET', $path);

        self::assertResponseIsSuccessful();
        $external = [];
        foreach ($crawler->filter('script[src], link[href][rel~="stylesheet"], link[rel~="preload"][href], img[src], iframe[src], source[src], form[action]')->each(
            static fn (Crawler $node): string => (string) ($node->attr('src') ?? $node->attr('href') ?? $node->attr('action')),
        ) as $url) {
            if (1 === preg_match('~^(?:https?:)?//(?!localhost(?::\d+)?/)~i', $url)) {
                $external[] = $url;
            }
        }
        self::assertSame([], $external, 'The CSP allows the origin only: nothing may be loaded from elsewhere on ' . $path);
    }

    /** @return iterable<string, array{string, bool}> */
    public static function provideKeyPages(): iterable
    {
        yield 'home' => ['/fr_FR/', false];
        yield 'login' => ['/fr_FR/login', false];
        yield 'admin login' => ['/admin/login', false];
        yield 'cart' => ['/fr_FR/cart/', true];
        yield 'checkout address' => ['/fr_FR/checkout/address', true];
    }

    public function testTheAdminDashboardRendersWithTheOutboundGuardActive(): void
    {
        $client = self::createClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $admin = $em->getRepository(\Sylius\Component\Core\Model\AdminUserInterface::class)->findOneBy(['username' => 'sophie']);
        self::assertInstanceOf(\Sylius\Component\Core\Model\AdminUserInterface::class, $admin);
        $client->loginUser($admin, 'admin');

        $client->request('GET', '/admin/');

        self::assertResponseIsSuccessful();
    }

    // ---- helpers

    private function postLogin(KernelBrowser $client, string $path, string $visitorIp): void
    {
        $client->request('POST', $path, ['_username' => 'nobody@example.test', '_password' => 'wrong'], server: ['HTTP_X_FORWARDED_FOR' => $visitorIp]);
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

    /** Puts the first item of the shared list "Atelier" in the cart of the signed-in member. */
    private function fillCart(KernelBrowser $client): void
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

    private function setupRequest(): string
    {
        $credentials = self::getContainer()->get(BuyerCredentialsProvider::class);
        $state = self::getContainer()->get(SignedState::class);
        self::assertInstanceOf(BuyerCredentialsProvider::class, $credentials);
        self::assertInstanceOf(SignedState::class, $state);

        return (new SetupRequestBuilder(new NativeClock()))->build(
            $credentials->provide(),
            $state->sign(['nonce' => bin2hex(random_bytes(4))]),
            'https://erp.acme.example/demo/punchout-simulator/return',
        );
    }

    /** A cart state the simulator would have signed for a session that came back from the shop. */
    private function orderState(): string
    {
        $state = self::getContainer()->get(SignedState::class);
        self::assertInstanceOf(SignedState::class, $state);
        $cookie = $state->sign(['nonce' => bin2hex(random_bytes(8))]);

        return $state->sign(['cookie' => $cookie, 'currency' => 'EUR', 'total' => '12.50', 'lines' => [$this->line()]]);
    }

    /** @return array<string, mixed> */
    private function line(): array
    {
        return ['supplierPartId' => 'VAR-1', 'supplierPartAuxId' => 'v1:VAR-1', 'description' => 'Gants', 'quantity' => 1, 'unitPrice' => '12.50', 'currency' => 'EUR', 'unitOfMeasure' => 'EA'];
    }

    /** Starts a local HTTP server standing for the PunchOut endpoint the simulator calls. */
    private function startFakeEndpoint(): void
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        self::assertNotFalse($socket);
        $port = (int) substr((string) stream_socket_get_name($socket, false), (int) strrpos((string) stream_socket_get_name($socket, false), ':') + 1);
        fclose($socket);

        $this->captureFile = sys_get_temp_dir() . '/demo-capture-' . bin2hex(random_bytes(4)) . '.log';
        $process = proc_open(
            [\PHP_BINARY, '-S', '127.0.0.1:' . $port, __DIR__ . '/Fixtures/cxml-endpoint-router.php'],
            [0 => ['file', '/dev/null', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes,
            null,
            ['CAPTURE_FILE' => $this->captureFile] + getenv(),
        );
        self::assertIsResource($process);
        $this->endpoint = $process;
        Await::listening('127.0.0.1', $port, 5);
        $this->setEnvironment(['DEMO_SIMULATOR_BASE_URL' => 'http://127.0.0.1:' . $port]);
    }

    /** @return list<array<mixed>> the headers of every call the fake endpoint received */
    private function captured(): array
    {
        if (!is_file($this->captureFile)) {
            return [];
        }

        $calls = [];
        foreach (array_filter(explode("\n", (string) file_get_contents($this->captureFile))) as $line) {
            $call = json_decode($line, true, 512, \JSON_THROW_ON_ERROR);
            self::assertIsArray($call);
            $calls[] = $call;
        }

        return $calls;
    }

    /** The token the order form of the simulator would carry, bound to the session of the client. */
    private function orderToken(KernelBrowser $client): string
    {
        $client->request('GET', '/demo/punchout-simulator');
        $request = $client->getRequest();
        $stack = new RequestStack();
        $stack->push($request);
        $token = new CsrfTokenManager(null, new SessionTokenStorage($stack))->getToken('demo_punchout_simulator_order')->getValue();
        $request->getSession()->save();

        return $token;
    }
}
