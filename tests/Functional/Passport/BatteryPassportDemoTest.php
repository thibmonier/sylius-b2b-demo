<?php

declare(strict_types=1);

namespace App\Tests\Functional\Passport;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\BatteryCategory;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

/** US-284 / T-TECH-55-03: the demo carries the passport of an industrial LFP forklift battery, in the scope of art. 77 of Regulation (EU) 2023/1542. */
final class BatteryPassportDemoTest extends WebTestCase
{
    public function testTheBatteryIsAnIndustrialOneAboveTwoKilowattHours(): void
    {
        self::bootKernel();
        $battery = $this->passport()->getBatteryProfile();

        self::assertNotNull($battery);
        self::assertSame(BatteryCategory::Industrial, $battery->category);
        $kilowattHours = (float) $battery->nominalCapacityAh * (float) $battery->nominalVoltageV / 1000;
        self::assertGreaterThan(2.0, $kilowattHours);
    }

    public function testThePublicPageShowsTheBatterySectionInFrench(): void
    {
        $client = self::createClient();

        $client->request('GET', '/fr_FR/product-passport/' . $this->passport()->getId());

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Lithium-fer-phosphate (LFP)', $content);
        self::assertStringContainsString('560', $content);
    }

    public function testTheProductPageLinksToThePassport(): void
    {
        $client = self::createClient();

        $client->request('GET', '/fr_FR/products/batt-001-fr');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('/product-passport/' . $this->passport()->getId(), (string) $client->getResponse()->getContent());
    }

    private function passport(): DigitalProductPassport
    {
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        \assert($entityManager instanceof EntityManagerInterface);

        foreach ($entityManager->getRepository(DigitalProductPassport::class)->findAll() as $passport) {
            if ('BATT-001' === $passport->getProductVariant()->getProduct()?->getCode()) {
                return $passport;
            }
        }

        self::fail('The battery passport of BATT-001 is not loaded.');
    }
}
