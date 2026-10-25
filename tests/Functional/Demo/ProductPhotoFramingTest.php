<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductImage;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Environment;
use Twig\Runtime\EscaperRuntime;

/**
 * US-197 (tranche 1): the card (3:4) and the product page (4:3) keep the subject of the photo in the frame. The photo is served as a
 * 4:3 file and cut by the browser (`object-position`) around the point focal of the manifest; without a point focal the framing is the
 * default one (centered), as before.
 */
final class ProductPhotoFramingTest extends WebTestCase
{
    use DatabaseAccess;

    public function testTheCardFramesAPhotoOnItsPointFocal(): void
    {
        $html = $this->card('EPI-CASK-001');

        self::assertStringContainsString('object-position: 100% 50%', $html);
    }

    public function testTheCardOfAPhotoWithoutPointFocalKeepsTheDefaultFraming(): void
    {
        $html = $this->card('EPI-GANT-002');

        self::assertStringNotContainsString('object-position', $html);
    }

    public function testTheCardServesTheFourByThreeFileSoThatTheBrowserCanFrameIt(): void
    {
        $html = $this->card('EPI-CASK-001');

        self::assertStringContainsString('width="800" height="600"', $html);
        self::assertStringNotContainsString('sylius_shop_product_thumbnail', $html);
        self::assertStringContainsString('400w', $html);
    }

    public function testTheProductPageFramesAPhotoOnItsPointFocal(): void
    {
        $client = self::createClient();
        $client->request('GET', '/fr_FR/products/' . $this->slug('EPI-CASK-001'));

        self::assertResponseIsSuccessful();
        $image = $client->getCrawler()->filter('img#main-image');
        self::assertCount(1, $image);
        self::assertStringContainsString('object-position: 100% 50%', (string) $image->attr('style'));
        self::assertSame('800', $image->attr('width'));
        self::assertSame('600', $image->attr('height'));
    }

    public function testTheProductPageOfAPhotoWithoutPointFocalKeepsTheDefaultFraming(): void
    {
        $client = self::createClient();
        $client->request('GET', '/fr_FR/products/' . $this->slug('EPI-GANT-002'));

        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('object-position', (string) $client->getCrawler()->filter('img#main-image')->attr('style'));
    }

    private function slug(string $code): string
    {
        self::bootKernel();

        return $this->stringValue("SELECT t.slug FROM sylius_product_translation t JOIN sylius_product p ON p.id = t.translatable_id WHERE p.code = ? AND t.locale = 'fr_FR'", [$code]);
    }

    private function card(string $code): string
    {
        self::bootKernel();
        $twig = self::getContainer()->get('twig');
        self::assertInstanceOf(Environment::class, $twig);
        $product = new Product();
        $product->setCode($code);
        $product->setCurrentLocale('fr_FR');
        $product->setFallbackLocale('fr_FR');
        $product->setName('Produit');
        $image = new ProductImage();
        $image->setType('main');
        $image->setPath('ab/cd/photo.webp');
        $product->addImage($image);

        return $twig->render('@SyliusShop/shared/components/main_image.html.twig', ['product' => $product, 'attributes' => new ComponentAttributes([], $twig->getRuntime(EscaperRuntime::class))]);
    }
}
