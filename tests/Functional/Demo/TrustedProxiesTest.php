<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * US-032 / T-032-02: trusted hosts and proxies, seen through real requests. Run against a database loaded
 * with the `demo` fixture suite.
 */
final class TrustedProxiesTest extends WebTestCase
{
    use EnvironmentSwitch;

    public function testAHostOutsideTheWhitelistAnswers400(): void
    {
        $this->setEnvironment(['TRUSTED_HOSTS' => '^demo\.example\.test$']);
        $client = self::createClient();

        $client->request('GET', '/fr_FR/', server: ['HTTP_HOST' => 'evil.example']);

        self::assertResponseStatusCodeSame(400);
    }

    public function testTheListedHostAndTheRailwayHealthcheckAreServed(): void
    {
        $this->setEnvironment(['TRUSTED_HOSTS' => '^demo\.example\.test$']);
        $client = self::createClient();

        $client->request('GET', '/fr_FR/', server: ['HTTP_HOST' => 'demo.example.test']);
        self::assertResponseIsSuccessful();

        $client->request('GET', '/health', server: ['HTTP_HOST' => 'healthcheck.railway.app']);
        self::assertResponseStatusCodeSame(200);
    }

    public function testTheRailwayDomainAndThePublicHostAreBothServedDuringTheTransition(): void
    {
        $this->setEnvironment(['TRUSTED_HOSTS' => '^demo\.example\.test$', 'DEMO_PUBLIC_HOST' => 'sylius-b2b.site.example']);
        $client = self::createClient();

        $client->request('GET', '/fr_FR/', server: ['HTTP_HOST' => 'demo.example.test']);
        self::assertResponseIsSuccessful();

        $client->request('GET', '/fr_FR/', server: ['HTTP_HOST' => 'sylius-b2b.site.example']);
        self::assertResponseIsSuccessful();

        $client->request('GET', '/health', server: ['HTTP_HOST' => 'healthcheck.railway.app']);
        self::assertResponseStatusCodeSame(200);
    }

    public function testTheMcpGuideAnnouncesTheHostOfTheRequest(): void
    {
        $this->setEnvironment(['TRUSTED_HOSTS' => '^demo\.example\.test$', 'DEMO_PUBLIC_HOST' => 'sylius-b2b.site.example', 'DEFAULT_URI' => 'https://demo.example.test']);
        $client = self::createClient();

        $client->request('GET', '/fr_FR/mcp', server: ['HTTP_HOST' => 'sylius-b2b.site.example']);

        self::assertResponseIsSuccessful();
        $crawler = $client->getCrawler();
        self::assertStringContainsString('sylius-b2b.site.example/_mcp', (string) $client->getResponse()->getContent());
        // The address announced for the server and its configuration is the one of the request, not DEFAULT_URI.
        self::assertStringNotContainsString('demo.example.test', $crawler->filter('[data-mcp-server-url]')->text() . $crawler->filter('[data-mcp-config]')->text());
        // The canonical address of the page (US-078) is the one of DEFAULT_URI, whatever host the visitor came by.
        self::assertSame('https://demo.example.test/fr_FR/mcp', $crawler->filter('link[rel="canonical"]')->attr('href'));
    }

    public function testAnUnknownHostStaysRefusedWithAPublicHost(): void
    {
        $this->setEnvironment(['TRUSTED_HOSTS' => '^demo\.example\.test$', 'DEMO_PUBLIC_HOST' => 'sylius-b2b.site.example']);
        $client = self::createClient();

        $client->request('GET', '/fr_FR/', server: ['HTTP_HOST' => 'evil.example']);

        self::assertResponseStatusCodeSame(400);
    }

    public function testWithoutAPublicHostOnlyTheRailwayDomainIsAccepted(): void
    {
        $this->setEnvironment(['TRUSTED_HOSTS' => '^demo\.example\.test$', 'DEMO_PUBLIC_HOST' => '']);
        $client = self::createClient();

        $client->request('GET', '/fr_FR/', server: ['HTTP_HOST' => 'sylius-b2b.site.example']);

        self::assertResponseStatusCodeSame(400);
    }

    public function testTheBadgeCookieIsSecureBehindATrustedHttpsProxy(): void
    {
        $client = self::createClient();

        $client->request('GET', '/demo/badges/off', server: ['HTTP_X_FORWARDED_PROTO' => 'https']);

        self::assertStringContainsString('secure', $this->badgeCookieHeader($client->getResponse()->headers->all('set-cookie')));
    }

    public function testTheBadgeCookieIsNotSecureOnPlainHttp(): void
    {
        $client = self::createClient();

        $client->request('GET', '/demo/badges/off');

        self::assertStringNotContainsString('secure', $this->badgeCookieHeader($client->getResponse()->headers->all('set-cookie')));
    }

    public function testTheProtoHeaderOfAnUntrustedClientIsIgnored(): void
    {
        $this->setEnvironment(['TRUSTED_PROXIES' => '10.9.9.9']);
        $client = self::createClient();

        $client->request('GET', '/demo/badges/off', server: ['HTTP_X_FORWARDED_PROTO' => 'https', 'HTTP_X_FORWARDED_FOR' => '198.51.100.9']);

        self::assertStringNotContainsString('secure', $this->badgeCookieHeader($client->getResponse()->headers->all('set-cookie')));
        self::assertSame('127.0.0.1', $client->getRequest()->getClientIp(), 'The visitor address of an untrusted client stays the connection address.');
    }

    public function testAbsoluteUrlsAreHttpsBehindATrustedHttpsProxy(): void
    {
        $client = self::createClient();
        $client->request('GET', '/fr_FR/', server: ['HTTP_X_FORWARDED_PROTO' => 'https', 'HTTP_X_FORWARDED_HOST' => 'localhost']);

        $router = self::getContainer()->get('router');
        self::assertInstanceOf(RouterInterface::class, $router);

        self::assertStringStartsWith('https://', $router->generate('app_demo_accounts', [], UrlGeneratorInterface::ABSOLUTE_URL));
    }

    public function testTheVisitorAddressComesFromATrustedProxyHeader(): void
    {
        $client = self::createClient();

        $client->request('GET', '/health', server: ['HTTP_X_FORWARDED_FOR' => '198.51.100.9']);

        self::assertSame('198.51.100.9', $client->getRequest()->getClientIp());
    }

    /** @param list<string|null> $headers */
    private function badgeCookieHeader(array $headers): string
    {
        foreach ($headers as $header) {
            if (str_starts_with((string) $header, 'demo_badges=')) {
                return strtolower((string) $header);
            }
        }
        self::fail('The demo_badges cookie was not set.');
    }
}
