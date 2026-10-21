<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use App\Tests\Functional\Demo\DatabaseAccess;
use PHPUnit\Framework\Attributes\DataProvider;
use Sylius\Component\Core\Model\Product;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;
use Twig\Runtime\EscaperRuntime;

/**
 * US-078: link previews and loading speed. The demo stays out of the search engines (noindex), but every page has a
 * description in the visitor's language, share metadata, and images that reserve their space and load when needed.
 */
final class SeoMetadataTest extends WebTestCase
{
    use DatabaseAccess;

    private ?KernelBrowser $client = null;

    /** @return iterable<string, array{string, string}> */
    public static function pages(): iterable
    {
        foreach (['fr_FR', 'en_US'] as $locale) {
            yield $locale . ' home' => [$locale, '/' . $locale . '/'];
            yield $locale . ' category' => [$locale, '/' . $locale . '/taxons/visserie'];
            yield $locale . ' plugins' => [$locale, '/' . $locale . '/plugins'];
            yield $locale . ' login' => [$locale, '/' . $locale . '/login'];
            yield $locale . ' mcp' => [$locale, '/' . $locale . '/mcp'];
            yield $locale . ' about' => [$locale, '/' . $locale . '/a-propos'];
            yield $locale . ' integrate' => [$locale, '/' . $locale . '/integrer'];
        }
    }

    #[DataProvider('pages')]
    public function testEveryPageHasADescriptionAndShareMetadata(string $locale, string $path): void
    {
        $crawler = $this->open($path);

        $this->assertDescriptionAndShareMetadata($crawler, $locale);
    }

    #[DataProvider('locales')]
    public function testTheProductPageHasADescriptionAndAShareImageOfTheProduct(string $locale): void
    {
        $this->client();
        $crawler = $this->open('/' . $locale . '/products/' . $this->productSlug($locale));

        $this->assertDescriptionAndShareMetadata($crawler, $locale);
        self::assertStringContainsString('/media/cache/', $this->meta($crawler, 'og:image'), 'The preview shows the product.');
    }

    #[DataProvider('locales')]
    public function testTheDescriptionIsWrittenInTheLanguageOfTheVisitorAndDiffersFromPageToPage(string $locale): void
    {
        $home = $this->meta($this->open('/' . $locale . '/'), 'description', 'name');
        $category = $this->meta($this->open('/' . $locale . '/taxons/visserie'), 'description', 'name');
        $plugins = $this->meta($this->open('/' . $locale . '/plugins'), 'description', 'name');

        self::assertCount(3, array_unique([$home, $category, $plugins]), 'Three pages, three descriptions.');
    }

    public function testTheTwoLanguagesDoNotShareTheirDescription(): void
    {
        self::assertNotSame(
            $this->meta($this->open('/fr_FR/'), 'description', 'name'),
            $this->meta($this->open('/en_US/'), 'description', 'name'),
        );
    }

    public function testTheCategoryTitleNamesTheCategory(): void
    {
        $crawler = $this->open('/fr_FR/taxons/visserie');

        self::assertStringContainsString('Visserie', $crawler->filter('title')->text());
    }

    #[DataProvider('pages')]
    public function testNoIndexIsKeptOnEveryPage(string $locale, string $path): void
    {
        $this->open($path);

        self::assertSame('noindex, nofollow', $this->client()->getResponse()->headers->get('X-Robots-Tag'));
        self::assertSame(0, $this->client()->getCrawler()->filter('meta[name="robots"][content*="index"]:not([content*="noindex"])')->count());
    }

    public function testTheCanonicalUrlKeepsThePageAndDropsTheOtherParameters(): void
    {
        $crawler = $this->open('/fr_FR/taxons/visserie?page=2&sorting%5Bprice%5D=asc');

        $canonical = $crawler->filter('link[rel="canonical"]');
        self::assertCount(1, $canonical);
        self::assertMatchesRegularExpression('#^https?://[^/?]+/fr_FR/taxons/visserie\?page=2$#', (string) $canonical->attr('href'));
        self::assertSame($canonical->attr('href'), $this->meta($crawler, 'og:url'));
    }

    public function testCategoryImagesReserveTheirSpaceAndLoadWhenTheyApproachTheScreen(): void
    {
        $images = $this->open('/fr_FR/taxons/visserie')->filter('main img[src*="/media/cache/"]');

        self::assertGreaterThan(0, $images->count());
        foreach ($images as $image) {
            $img = new Crawler($image);
            self::assertMatchesRegularExpression('/^\d+$/', (string) $img->attr('width'));
            self::assertMatchesRegularExpression('/^\d+$/', (string) $img->attr('height'));
            self::assertSame('lazy', $img->attr('loading'));
            self::assertSame('async', $img->attr('decoding'));
            self::assertStringContainsString('w,', (string) $img->attr('srcset'), 'Two widths are offered.');
            self::assertNotEmpty($img->attr('sizes'));
        }
    }

    public function testTheMainImageOfAProductIsFetchedFirstAndSizedBeforeItArrives(): void
    {
        $this->client();
        $crawler = $this->open('/fr_FR/products/' . $this->productSlug('fr_FR'));

        $main = $crawler->filter('img#main-image');
        self::assertCount(1, $main);
        self::assertSame('high', $main->attr('fetchpriority'));
        self::assertNotSame('lazy', $main->attr('loading'));
        self::assertMatchesRegularExpression('/^\d+$/', (string) $main->attr('width'));
        self::assertMatchesRegularExpression('/^\d+$/', (string) $main->attr('height'));
        self::assertStringContainsString('w,', (string) $main->attr('srcset'));
        self::assertStringNotContainsString('sylius_shop_product_large_thumbnail', (string) $main->attr('src'), 'The 1200 px version is not the one a phone downloads.');
    }

    public function testTheImagesBelowTheMainOneOfAProductPageAreLazy(): void
    {
        $this->client();
        $crawler = $this->open('/fr_FR/products/' . $this->productSlug('fr_FR'));

        $others = $crawler->filter('main img[src*="/media/cache/"]:not(#main-image)');
        foreach ($others as $image) {
            self::assertSame('lazy', (new Crawler($image))->attr('loading'));
        }
    }

    public function testAProductWithoutImageGetsAFallbackVisualWithoutError(): void
    {
        self::bootKernel();
        $twig = self::getContainer()->get('twig');
        self::assertInstanceOf(Environment::class, $twig);
        $product = new Product();
        $product->setCurrentLocale('fr_FR');
        $product->setFallbackLocale('fr_FR');
        $product->setName('Sans image');

        $html = $twig->render('@SyliusShop/shared/components/main_image.html.twig', ['product' => $product, 'attributes' => new ComponentAttributes([], $twig->getRuntime(EscaperRuntime::class))]);

        self::assertStringContainsString('data:image/svg+xml', $html, 'The fallback is inline: no request that could fail.');
        self::assertStringContainsString('width="', $html);
        self::assertStringContainsString('height="', $html);
        self::assertStringContainsString('Sans image', $html);
    }

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'fr_FR' => ['fr_FR'];
        yield 'en_US' => ['en_US'];
    }

    private function assertDescriptionAndShareMetadata(Crawler $crawler, string $locale): void
    {
        $description = $this->meta($crawler, 'description', 'name');
        self::assertGreaterThanOrEqual(40, mb_strlen($description), 'A description says something.');
        self::assertLessThanOrEqual(160, mb_strlen($description), 'A description fits a search result.');
        self::assertSame($description, $this->meta($crawler, 'og:description'));
        self::assertNotSame('', $this->meta($crawler, 'og:title'));
        self::assertSame($crawler->filter('title')->text(), $this->meta($crawler, 'og:title'));
        self::assertSame('website', $this->meta($crawler, 'og:type'));
        self::assertSame($locale, $this->meta($crawler, 'og:locale'));
        self::assertMatchesRegularExpression('#^https?://[^/]+/.+\.(png|webp|jpg)$#', $this->meta($crawler, 'og:image'));
        self::assertSame('summary_large_image', $this->meta($crawler, 'twitter:card', 'name'));
        self::assertSame($this->meta($crawler, 'og:image'), $this->meta($crawler, 'twitter:image', 'name'));
    }

    private function meta(Crawler $crawler, string $key, string $attribute = 'property'): string
    {
        $tag = $crawler->filter(\sprintf('meta[%s="%s"]', $attribute, $key));
        self::assertCount(1, $tag, $key);

        return (string) $tag->attr('content');
    }

    private function open(string $path): Crawler
    {
        $crawler = $this->client()->request('GET', $path);
        self::assertResponseIsSuccessful($path);

        return $crawler;
    }

    private function client(): KernelBrowser
    {
        return $this->client ??= self::createClient();
    }

    private function productSlug(string $locale): string
    {
        return $this->stringValue("SELECT t.slug FROM sylius_product_translation t JOIN sylius_product p ON p.id = t.translatable_id WHERE p.code = 'VISS-001' AND t.locale = ?", [$locale]);
    }
}
