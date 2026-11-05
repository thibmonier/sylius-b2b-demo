<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Channel\DemoChannelContext;
use App\Tests\Support\BuildsDemoCarts;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-228: the published theme (tmonier/sylius-industry-trade-theme) is active on the B2B channel only. The journey pages
 * answer 200 on both channels; the theme marker (body[data-theme="industry-trade"]) is on B2B and never on B2C, a channel
 * without the theme. Product images are present on the list and on the product page of B2B.
 */
final class ThemeChannelsTest extends WebTestCase
{
    use BuildsDemoCarts;

    private const MARKER = 'body[data-theme="industry-trade"]';

    /** @return iterable<string, array{string}> */
    public static function visitorPages(): iterable
    {
        yield 'home' => ['/fr_FR/'];
        yield 'list' => ['/fr_FR/taxons/quincaillerie'];
        yield 'product' => ['/fr_FR/products/outi-001-fr'];
    }

    #[DataProvider('visitorPages')]
    public function testTheB2bChannelServesThePageWithTheTheme(string $path): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');

        $page = $client->request('GET', $path);

        self::assertResponseStatusCodeSame(200);
        self::assertCount(1, $page->filter(self::MARKER), $path);
    }

    #[DataProvider('visitorPages')]
    public function testTheB2cChannelKeepsTheStandardPagesWithoutTheTheme(string $path): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2C');

        $page = $client->request('GET', $path);

        self::assertResponseStatusCodeSame(200);
        self::assertCount(0, $page->filter(self::MARKER), $path);
        self::assertStringNotContainsString('industry-', (string) $client->getResponse()->getContent());
    }

    public function testTheCartIsServedWithTheThemeOnB2b(): void
    {
        $client = $this->clientOf('claire@acme.example');
        $this->cartOf('claire@acme.example', ['EPI-CASK-001' => 2]);

        $page = new Crawler($this->get($client, '/fr_FR/cart/'));

        self::assertCount(1, $page->filter(self::MARKER));
    }

    public function testTheCartIsServedWithoutTheThemeOnB2c(): void
    {
        $client = $this->clientOf('lea@demo.example');
        $client->getCookieJar()->set(new Cookie(DemoChannelContext::COOKIE, DemoChannelContext::B2C));
        $this->cartOf('lea@demo.example', ['EMBA-001' => 2], 'B2C');

        $page = new Crawler($this->get($client, '/fr_FR/cart/'));

        self::assertCount(0, $page->filter(self::MARKER));
    }

    /** @return iterable<string, array{string}> */
    public static function pagesWithProductImages(): iterable
    {
        yield 'list' => ['/fr_FR/taxons/quincaillerie'];
        yield 'product' => ['/fr_FR/products/outi-001-fr'];
    }

    #[DataProvider('pagesWithProductImages')]
    public function testTheProductImagesOfB2bHaveASource(string $path): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');

        $page = $client->request('GET', $path);

        $images = $page->filter('main img[src*="/media/cache/"]');
        self::assertGreaterThan(0, $images->count(), 'Product images on ' . $path);
        foreach ($images->each(static fn (Crawler $image): ?string => $image->attr('src')) as $source) {
            self::assertNotSame('', trim((string) $source));
        }
    }
}
