<?php

declare(strict_types=1);

namespace App\Tests\Functional\Passport;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** US-241: public passport page shows origin, recyclability, repairability, version and revision date. */
final class PublicPageContentTest extends WebTestCase
{
    public function testPassportPageShowsOriginMaterialsRecyclabilityAndRepairability(): void
    {
        $client = static::createClient();

        // Load passport for EPI-CASK-001 (fixture creates passports for 3 products)
        $client->request('GET', '/fr_FR/product-passport/1');

        self::assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);

        // Origin materials
        self::assertStringContainsString('Polyéthylène haute densité (HDPE)', $content);
        self::assertStringContainsString('France', $content);

        // Recyclability
        self::assertStringContainsString('recyclable', $content);
        self::assertStringContainsString('85%', $content);

        // Repairability
        self::assertStringContainsString('Suspension remplaçable', $content);
        self::assertStringContainsString('7/10', $content);
    }

    public function testPassportPageShowsVersionAndRevisionDate(): void
    {
        $client = static::createClient();

        $client->request('GET', '/fr_FR/product-passport/1');

        self::assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);

        // Version
        self::assertStringContainsString('Version', $content);
        self::assertStringContainsString('1', $content);

        // Date should be present (format may vary)
        self::assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}/', $content);
    }

    public function testPassportWithNoDataReturns404(): void
    {
        $client = static::createClient();

        // Try to access a passport that doesn't exist or has no data
        $client->request('GET', '/fr_FR/product-passport/999');

        self::assertResponseStatusCodeSame(404);
    }
}
