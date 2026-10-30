<?php

declare(strict_types=1);

namespace App\Tests\Functional\Passport;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** US-241: product page shows passport link only if a passport exists for this product. */
final class ProductWithoutPassportTest extends WebTestCase
{
    public function testProductWithPassportShowsPassportLink(): void
    {
        $client = static::createClient();

        // EPI-CASK-001 has a passport (created by demo_digital_passports fixture)
        $client->request('GET', '/fr_FR/products/epi-cask-001-fr');

        self::assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);

        // The "Product Passport" link should be present
        self::assertStringContainsString('Passeport Produit', $content);
    }

    public function testProductWithoutPassportDoesNotShowPassportLink(): void
    {
        $client = static::createClient();

        // Pick a product without passport (e.g., OUTI-001 - tool)
        $client->request('GET', '/fr_FR/products/outi-001-fr');

        self::assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);

        // The "Product Passport" link should NOT be present
        self::assertStringNotContainsString('Passeport Produit', $content);
        self::assertStringNotContainsString('product-passport', $content);
    }

    public function testEnglishProductWithPassportShowsEnglishPassportLink(): void
    {
        $client = static::createClient();

        $client->request('GET', '/en_US/products/epi-cask-001-en');

        self::assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);

        // The "Product Passport" link should be in English
        self::assertStringContainsString('Product Passport', $content);
    }
}
