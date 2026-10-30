<?php

declare(strict_types=1);

namespace App\Tests\Functional\Passport;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-245: Smoke test verifying that the product passport link
 * on the product page leads to a working passport page (200)
 * showing the origin of materials.
 */
final class ProductPassportLinkSmokeTest extends WebTestCase
{
    public function testProductPagePassportLinkLeadsToWorkingPassportPageInFrench(): void
    {
        $client = static::createClient();

        // Step 1: Open the product page in French
        $crawler = $client->request('GET', '/fr_FR/products/epi-cask-001-fr');
        self::assertResponseIsSuccessful();

        // Step 2: Find and click the passport link
        $links = $crawler->selectLink('Passeport Produit');
        self::assertGreaterThan(0, $links->count(), 'The product page should show a "Passeport Produit" link');
        $link = $links->link();
        $client->click($link);

        // Step 3: Verify the passport page responds 200
        self::assertResponseIsSuccessful();

        // Step 4: Verify the page shows origin of materials
        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);

        // From fixture: "Polyéthylène haute densité (HDPE) : France (Rhône-Alpes). Suspension textile : Portugal."
        self::assertStringContainsString('Polyéthylène', $content);
        self::assertStringContainsString('France', $content);
        self::assertStringContainsString('Rhône-Alpes', $content);
    }

    public function testProductPagePassportLinkLeadsToWorkingPassportPageInEnglish(): void
    {
        $client = static::createClient();

        // Step 1: Open the product page in English
        $crawler = $client->request('GET', '/en_US/products/epi-cask-001-en');
        self::assertResponseIsSuccessful();

        // Step 2: Find and click the passport link
        $links = $crawler->selectLink('Product Passport');
        self::assertGreaterThan(0, $links->count(), 'The product page should show a "Product Passport" link');
        $link = $links->link();
        $client->click($link);

        // Step 3: Verify the passport page responds 200
        self::assertResponseIsSuccessful();

        // Step 4: Verify the page shows origin of materials (content is in French in the fixture)
        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);

        // From fixture: same content, labels in English
        self::assertStringContainsString('Polyéthylène', $content);
        self::assertStringContainsString('France', $content);
        self::assertStringContainsString('Rhône-Alpes', $content);
    }
}
