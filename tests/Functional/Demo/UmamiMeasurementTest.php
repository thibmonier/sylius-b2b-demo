<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-143: the Umami script is rendered on the public pages of a configured demo (FR and EN), with the CSP extended
 * and no cookie of its own, and nowhere else. Run against a database loaded with the `demo` fixture suite.
 */
final class UmamiMeasurementTest extends WebTestCase
{
    use EnvironmentSwitch;

    private const SCRIPT = 'https://cloud.umami.is/script.js';

    private const ID = '0f8fad5b-d9cb-469f-a165-70867728950e';

    /** @return iterable<string, array{string}> */
    public static function publicPages(): iterable
    {
        yield 'home FR' => ['/fr_FR/'];
        yield 'home EN' => ['/en_US/'];
        yield 'plugins FR' => ['/fr_FR/plugins'];
        yield 'legal notice EN' => ['/en_US/mentions-legales'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('publicPages')]
    public function testTheScriptIsLoadedDeferredWithTheSiteIdAndTheCspAllowsItsHost(string $path): void
    {
        $this->setEnvironment(['DEMO_UMAMI_SCRIPT_URL' => self::SCRIPT, 'DEMO_UMAMI_WEBSITE_ID' => self::ID]);
        $client = self::createClient();

        $crawler = $client->request('GET', $path);

        self::assertResponseIsSuccessful();
        $script = $crawler->filter('script[src="' . self::SCRIPT . '"]');
        self::assertCount(1, $script);
        self::assertSame(self::ID, $script->attr('data-website-id'));
        self::assertNotNull($script->attr('defer'));
        self::assertSame('true', $script->attr('data-do-not-track'));
        $csp = (string) $client->getResponse()->headers->get('Content-Security-Policy');
        self::assertStringContainsString("script-src 'self' https://cloud.umami.is", $csp);
        self::assertStringContainsString("connect-src 'self' https://cloud.umami.is", $csp);
        self::assertStringNotContainsString('umami', strtolower(implode(';', $client->getResponse()->headers->all('set-cookie'))));
    }

    public function testNothingIsRenderedAndTheCspIsUnchangedWhenUmamiIsNotConfigured(): void
    {
        $this->setEnvironment(['DEMO_UMAMI_SCRIPT_URL' => '', 'DEMO_UMAMI_WEBSITE_ID' => '']);
        $client = self::createClient();

        $client->request('GET', '/fr_FR/');

        self::assertStringNotContainsString('umami.is', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('umami', (string) $client->getResponse()->headers->get('Content-Security-Policy'));
    }

    public function testAnInvalidConfigurationDisablesTheMeasureWithoutAnError(): void
    {
        $this->setEnvironment(['DEMO_UMAMI_SCRIPT_URL' => 'http://cloud.umami.is/script.js', 'DEMO_UMAMI_WEBSITE_ID' => 'nope']);
        $client = self::createClient();

        $client->request('GET', '/fr_FR/');

        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('umami.is', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('umami', (string) $client->getResponse()->headers->get('Content-Security-Policy'));
    }

    public function testTheAdminLoginIsNotMeasured(): void
    {
        $this->setEnvironment(['DEMO_UMAMI_SCRIPT_URL' => self::SCRIPT, 'DEMO_UMAMI_WEBSITE_ID' => self::ID]);
        $client = self::createClient();

        $crawler = $client->request('GET', '/admin/login');

        self::assertCount(0, $crawler->filter('script[src*="umami"]'));
        self::assertStringNotContainsString('umami.is', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('umami', (string) $client->getResponse()->headers->get('Content-Security-Policy'));
    }

    public function testTheLegalNoticeDeclaresTheMeasureInBothLanguages(): void
    {
        $this->setEnvironment(['DEMO_UMAMI_SCRIPT_URL' => self::SCRIPT, 'DEMO_UMAMI_WEBSITE_ID' => self::ID]);
        $client = self::createClient();

        $fr = $client->request('GET', '/fr_FR/mentions-legales');
        self::assertStringContainsString('Umami', $fr->filter('[data-legal-audience]')->text());
        $en = $client->request('GET', '/en_US/mentions-legales');
        self::assertStringContainsString('cookie', $en->filter('[data-legal-audience]')->text());
    }

    public function testTheLegalNoticeSaysNothingAboutTheMeasureWhenDisabled(): void
    {
        $this->setEnvironment(['DEMO_UMAMI_SCRIPT_URL' => '', 'DEMO_UMAMI_WEBSITE_ID' => '']);
        $client = self::createClient();

        $crawler = $client->request('GET', '/fr_FR/mentions-legales');

        self::assertCount(0, $crawler->filter('[data-legal-audience]'));
    }

    public function testTheEventAttributesCarryTheDemoPrefixAndNoPersonalData(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/fr_FR/plugins');

        $guide = $crawler->filter('[data-plugin-guide-link]');
        self::assertSame('demo-guide-mcp', $guide->attr('data-umami-event'));
        self::assertSame('plugins', $guide->attr('data-umami-event-emplacement'));
        $github = $crawler->filter('a[href^="https://github.com/"][data-umami-event]');
        self::assertGreaterThan(0, $github->count());
        self::assertSame('demo-lien-github', $github->attr('data-umami-event'));
        self::assertMatchesRegularExpression('/^[a-z0-9_-]+$/', (string) $github->attr('data-umami-event-depot'));
    }

    public function testTheSimulatorIsMeasuredAndItsStartButtonCarriesAnEvent(): void
    {
        $this->setEnvironment(['DEMO_UMAMI_SCRIPT_URL' => self::SCRIPT, 'DEMO_UMAMI_WEBSITE_ID' => self::ID]);
        $client = self::createClient();

        $crawler = $client->request('GET', '/demo/punchout-simulator');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('script[src="' . self::SCRIPT . '"]'));
        self::assertSame('demo-simulateur-punchout-lancement', $crawler->filter('button[data-umami-event]')->attr('data-umami-event'));
    }
}
