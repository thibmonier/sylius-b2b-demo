<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Plugin\DemoPluginCatalog;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\RouterInterface;

/**
 * US-243: every "See it in the demo" button of the plugins page opens a usable page in public mode (registrations closed),
 * for a signed-out visitor: never a 403 nor a 404. A page that needs an account redirects to the login.
 */
final class PluginDemoLinksTest extends WebTestCase
{
    use EnvironmentSwitch;

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'fr' => ['fr_FR'];
        yield 'en' => ['en_US'];
    }

    #[DataProvider('locales')]
    public function testEveryDemoButtonOpensAUsablePageInPublicMode(string $locale): void
    {
        $this->setEnvironment(['DEMO_REGISTRATION_OPEN' => '0']);
        $client = self::createClient();

        $page = $client->request('GET', '/' . $locale . '/plugins');
        self::assertResponseIsSuccessful();
        $links = $page->filter('[data-plugin] [data-plugin-demo-link]');
        self::assertGreaterThan(0, $links->count());

        $links->each(function (Crawler $link) use ($client): void {
            $plugin = (string) $link->closest('[data-plugin]')?->attr('data-plugin');
            $href = (string) $link->attr('href');
            $client->restart();
            $client->request('GET', $href);
            $response = $client->getResponse();

            self::assertNotContains($response->getStatusCode(), [403, 404], sprintf('%s: its demo page %s answers %d in public mode.', $plugin, $href, $response->getStatusCode()));
            if ($response->isRedirection()) {
                self::assertStringEndsWith('/login', (string) $response->headers->get('Location'), sprintf('%s: %s should only redirect to the login.', $plugin, $href));
            }
        });
    }

    public function testThePunchOutDemoButtonOpensTheSimulatorInTheLanguageOfThePage(): void
    {
        $client = self::createClient();

        $page = $client->request('GET', '/en_US/plugins');
        $client->click($page->filter('[data-plugin="sylius-punchout-cxml-plugin"] [data-plugin-demo-link]')->link());

        self::assertResponseIsSuccessful();
        self::assertSame('en', $client->getCrawler()->filter('html')->attr('lang'));
    }

    public function testEveryDemoRouteOfTheCatalogueExists(): void
    {
        $container = self::getContainer();
        $catalog = $container->get(DemoPluginCatalog::class);
        self::assertInstanceOf(DemoPluginCatalog::class, $catalog);
        $router = $container->get('router');
        self::assertInstanceOf(RouterInterface::class, $router);

        foreach ($catalog->all() as $plugin) {
            if (null !== $plugin->demoRoute) {
                self::assertNotNull($router->getRouteCollection()->get($plugin->demoRoute), sprintf('%s: unknown demo route "%s".', $plugin->slug, $plugin->demoRoute));
            }
        }
    }
}
