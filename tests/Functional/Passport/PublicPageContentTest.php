<?php

declare(strict_types=1);

namespace App\Tests\Functional\Passport;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

/** US-241: public passport page shows origin, recyclability, repairability, version and revision date. */
final class PublicPageContentTest extends WebTestCase
{
    public function testPassportPageShowsOriginMaterialsRecyclabilityAndRepairability(): void
    {
        $client = static::createClient();
        
        $passport = $client->getContainer()->get('doctrine')->getRepository(DigitalProductPassport::class)->findOneBy([]);
        self::assertNotNull($passport, 'At least one passport should exist in fixtures');
        
        $client->request('GET', '/fr_FR/product-passport/' . $passport->getId());
        
        self::assertResponseIsSuccessful();
        
        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);
        
        // Origin materials from EPI-CASK-001 fixture
        self::assertStringContainsString('Polyéthylène', $content);
        self::assertStringContainsString('France', $content);
        
        // Recyclability
        self::assertStringContainsString('recyclable', $content);
        
        // Repairability
        self::assertStringContainsString('Suspension', $content);
    }

    public function testPassportPageShowsVersionAndRevisionDate(): void
    {
        $client = static::createClient();
        
        $passport = $client->getContainer()->get('doctrine')->getRepository(DigitalProductPassport::class)->findOneBy([]);
        self::assertNotNull($passport);
        
        $client->request('GET', '/fr_FR/product-passport/' . $passport->getId());
        
        self::assertResponseIsSuccessful();
        
        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);
        
        // Version
        self::assertStringContainsString('Version', $content);
        
        // Date should be present
        self::assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}/', $content);
    }

    public function testPassportWithNoDataReturns404(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/fr_FR/product-passport/999999');
        
        self::assertResponseStatusCodeSame(404);
    }
}
