<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use App\Demo\Account\DemoAccountCatalog;
use App\Demo\Plugin\DemoPluginCatalog;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;

/** US-014 — Plugins page (both channels, both locales) and the B2B homepage. */
final class PluginsAndHomepageTest extends WebTestCase
{
    /** @return iterable<string, array{string, string, string}> */
    public static function pluginPages(): iterable
    {
        yield 'fr' => ['/fr_FR/plugins', 'Voir dans la démo', 'Achats B2B'];
        yield 'en' => ['/en_US/plugins', 'See it in the demo', 'B2B purchasing'];
    }

    #[DataProvider('pluginPages')]
    public function testThePluginsPageIsPublicAndLocalised(string $path, string $demoLabel, string $category): void
    {
        $client = self::createClient();
        foreach (['B2B', 'B2C'] as $channel) {
            $client->request('GET', '/demo/channel/' . $channel);
            $client->request('GET', $path);

            self::assertResponseStatusCodeSame(200);
            $content = (string) $client->getResponse()->getContent();
            self::assertStringContainsString($demoLabel, $content);
            self::assertStringContainsString($category, $content);
        }
    }

    public function testEveryPluginHasACardWithItsGithubLink(): void
    {
        $client = self::createClient();
        $client->request('GET', '/en_US/plugins');
        $content = (string) $client->getResponse()->getContent();
        $catalog = self::catalog();

        self::assertCount(21, $client->getCrawler()->filter('[data-plugin]'));
        foreach ($catalog->all() as $plugin) {
            self::assertStringContainsString('data-plugin="' . $plugin->slug . '"', $content);
            self::assertStringContainsString($plugin->githubUrl(), $content);
            self::assertStringContainsString($plugin->descriptionEn, html_entity_decode($content, \ENT_QUOTES));
            self::assertStringContainsString($plugin->scenarioEn, html_entity_decode($content, \ENT_QUOTES));
            self::assertStringContainsString($plugin->advisedAccount, $content);
            self::assertSame(
                null !== $plugin->packagistUrl(),
                str_contains($content, 'href="' . $plugin->packagistUrl() . '"'),
                $plugin->slug,
            );
        }
        self::assertCount(1, $client->getCrawler()->filter('[data-plugin-unpublished]'));
        self::assertCount(20, $client->getCrawler()->filter('[data-plugin-packagist]'));
    }

    public function testEveryDemoRouteOfTheCatalogExists(): void
    {
        self::bootKernel();
        $router = self::getContainer()->get(RouterInterface::class);
        $catalog = self::catalog();
        self::assertInstanceOf(RouterInterface::class, $router);

        foreach ($catalog->all() as $plugin) {
            if (null === $plugin->demoRoute) {
                continue;
            }
            self::assertNotSame('', $router->generate($plugin->demoRoute, ['_locale' => 'fr_FR']), $plugin->slug);
        }
    }

    public function testTheB2bHomepageShowsQuickAccessAndJourneys(): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');
        $crawler = $client->request('GET', '/fr_FR/');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('[data-b2b-home="hero"]'));
        self::assertCount(1, $crawler->filter('[data-b2b-home="quick-access"] a[href="/demo/accounts"]'));
        self::assertCount(1, $crawler->filter('[data-b2b-home="quick-access"] a[href="/fr_FR/plugins"]'));
        self::assertGreaterThanOrEqual(3, $crawler->filter('[data-b2b-journey]')->count());
    }

    public function testTheB2cHomepageKeepsTheStockSyliusHome(): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2C');
        $crawler = $client->request('GET', '/fr_FR/');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('[data-b2b-home]'));
    }

    private static function catalog(): DemoPluginCatalog
    {
        $config = Yaml::parseFile(\dirname(__DIR__, 2) . '/config/demo/plugins.yaml');
        self::assertIsArray($config);

        $accounts = Yaml::parseFile(\dirname(__DIR__, 2) . '/config/demo/accounts.yaml');
        self::assertIsArray($accounts);
        $p = $accounts['parameters'];

        return new DemoPluginCatalog(
            $config['parameters']['demo.plugin_catalog'],
            new DemoAccountCatalog($p['demo.password'], $p['demo.companies'], $p['demo.consumers'], $p['demo.admin_users']),
        );
    }
}
