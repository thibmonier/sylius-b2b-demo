<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Demo\DataFixtures\ProductImageAttacher;
use App\Tests\Functional\Demo\DatabaseAccess;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-047: the demo products are illustrated from the images versioned in the repository (loaded fixtures judged).
 */
final class ProductImagesTest extends WebTestCase
{
    use DatabaseAccess;

    public function testEveryProductOfTheCatalogueHasItsMainImageOnDisk(): void
    {
        self::bootKernel();
        $directory = self::getContainer()->getParameter('sylius_core.images_dir');
        \assert(\is_string($directory));

        $rows = $this->db()->fetchAllAssociative(
            "SELECT p.code, i.path FROM sylius_product p LEFT JOIN sylius_product_image i ON i.owner_id = p.id AND i.type = 'main' WHERE p.code REGEXP '^(EPI|EMBA|SIGN|PAPE|VISS|OUTI|FIXA)-'",
        );

        self::assertCount(210, $rows);
        foreach ($rows as $row) {
            $code = \is_string($row['code']) ? $row['code'] : '';
            self::assertIsString($row['path'], $code . ' has no main image.');
            self::assertFileExists($directory . '/' . $row['path'], $code);
        }
    }

    public function testEveryProductHasExactlyOneImageRow(): void
    {
        self::bootKernel();

        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM (SELECT owner_id FROM sylius_product_image GROUP BY owner_id HAVING COUNT(*) > 1) t'));
    }

    public function testTheProductPageAndTheListShowTheImage(): void
    {
        $client = self::createClient();
        $slug = $this->stringValue("SELECT slug FROM sylius_product_translation t JOIN sylius_product p ON p.id = t.translatable_id WHERE p.code = 'VISS-001' AND t.locale = 'fr_FR'");

        $client->request('GET', '/fr_FR/products/' . $slug);
        self::assertResponseIsSuccessful();
        self::assertMatchesRegularExpression('#<img[^>]+/media/cache/[^"]+\.webp#', (string) $client->getResponse()->getContent(), 'The product page shows its image.');

        $client->request('GET', '/fr_FR/taxons/visserie');
        self::assertResponseIsSuccessful();
        self::assertMatchesRegularExpression('#<img[^>]+/media/cache/[^"]+\.webp#', (string) $client->getResponse()->getContent(), 'The list shows the images.');
    }

    public function testLoadingTheImagesNeverNeedsTheNetwork(): void
    {
        $constructor = (new \ReflectionClass(ProductImageAttacher::class))->getConstructor();
        self::assertNotNull($constructor);

        foreach ($constructor->getParameters() as $parameter) {
            $type = (string) $parameter->getType();
            self::assertStringNotContainsString('HttpClient', $type, 'The attacher only reads local files.');
        }
    }
}
