<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use App\Demo\Plugin\DemoPluginCatalog;
use App\Tests\Support\DemoCatalogs;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** US-087 (US-204: the Plugins page shows no figure, it lists fewer plugins than the catalogue counts): the number of plugins shown on the home and About pages is counted from config/demo/plugins.yaml. */
final class PluginCountSmokeTest extends WebTestCase
{
    /** @return iterable<string, array{string, string}> */
    public static function pages(): iterable
    {
        yield 'home fr' => ['/fr_FR/', 'montée avec %d plugins open source'];
        yield 'home en' => ['/en_US/', 'built with %d open source plugins'];
        yield 'about fr' => ['/fr_FR/a-propos', 'Les %d plugins ci-dessous'];
        yield 'about en' => ['/en_US/a-propos', 'The %d plugins below'];
    }

    #[DataProvider('pages')]
    public function testThePageShowsTheFigureCountedFromTheCatalogue(string $path, string $pattern): void
    {
        $client = self::createClient();
        $catalog = self::getContainer()->get(DemoPluginCatalog::class);
        self::assertInstanceOf(DemoPluginCatalog::class, $catalog);

        $client->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertSame(DemoCatalogs::pluginCount(), $catalog->count());
        self::assertStringContainsString(sprintf($pattern, $catalog->count()), html_entity_decode((string) $client->getResponse()->getContent(), \ENT_QUOTES));
        self::assertDoesNotMatchRegularExpression('/\b24 (open source )?plugins|plugins\s+24\b/i', strip_tags((string) $client->getResponse()->getContent()));
    }
}
