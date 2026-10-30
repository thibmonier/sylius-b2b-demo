<?php

declare(strict_types=1);

namespace App\Tests\Functional\Passport;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

/** US-241: passport page respects locale (FR/EN). */
final class LocalizationTest extends WebTestCase
{
    public function testPassportPageDisplaysInFrench(): void
    {
        $client = static::createClient();

        $passport = $client->getContainer()->get('doctrine')->getRepository(DigitalProductPassport::class)->findOneBy([]);
        self::assertNotNull($passport);

        $client->request('GET', '/fr_FR/product-passport/' . $passport->getId());

        self::assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);

        // French labels
        self::assertStringContainsString('Origine', $content);
        self::assertStringContainsString('Recyclabilité', $content);
        self::assertStringContainsString('Réparabilité', $content);
    }

    public function testPassportPageDisplaysInEnglish(): void
    {
        $client = static::createClient();

        $passport = $client->getContainer()->get('doctrine')->getRepository(DigitalProductPassport::class)->findOneBy([]);
        self::assertNotNull($passport);

        $client->request('GET', '/en_US/product-passport/' . $passport->getId());

        self::assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);

        // English labels
        self::assertStringContainsString('Origin', $content);
        self::assertStringContainsString('Recyclability', $content);
        self::assertStringContainsString('Repairability', $content);
    }
}
