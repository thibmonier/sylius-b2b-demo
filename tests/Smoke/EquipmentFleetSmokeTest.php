<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusCustomerAssetManagementPlugin\Entity\CustomerAsset;

/**
 * Sprint 21: Claire declares three fictional machines (customer-asset-management) and the catalogue narrows down to the
 * parts compatible with them. Run against a database loaded with the `demo` fixture suite.
 */
final class EquipmentFleetSmokeTest extends WebTestCase
{
    public function testClaireSeesHerFourMachines(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $crawler = $client->request('GET', '/fr_FR/account/equipment');

        self::assertResponseIsSuccessful();
        self::assertCount(4, $crawler->filter('[data-test-tmonier-equipment-asset]'));
        self::assertStringContainsString('Chariot élévateur C20', $crawler->filter('main')->text());
    }

    public function testTheToolsCategoryOffersTheCompatibilityFilterAndNarrowsTheList(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $all = $client->request('GET', '/fr_FR/taxons/outillage');
        self::assertResponseIsSuccessful();
        self::assertCount(1, $all->filter('[data-test-tmonier-equipment-filter]'));
        $unfiltered = $all->filter('[data-test-product-name], .sylius-product-name')->count();

        $filtered = $client->request('GET', '/fr_FR/taxons/outillage?equipment=all');
        self::assertResponseIsSuccessful();
        self::assertCount(0, $filtered->filter('[data-test-tmonier-equipment-filter-empty]'));
        self::assertLessThanOrEqual($unfiltered, $filtered->filter('[data-test-product-name], .sylius-product-name')->count());
    }

    public function testTheTorqueWrenchPageLinksToItsPassportAndItsCompatibleParts(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        \assert($em instanceof EntityManagerInterface);
        // A DQL query: the plugin's repository is not a tagged Doctrine service, so getRepository() cannot build it.
        $asset = $em->createQueryBuilder()->select('a')->from(CustomerAsset::class, 'a')
            ->where('a.identifier = :identifier')->setParameter('identifier', 'DEMO-0004')
            ->getQuery()->getOneOrNullResult();
        self::assertInstanceOf(CustomerAsset::class, $asset, 'DEMO-0004 must exist (demo_equipment_fleet).');

        $crawler = $client->request('GET', '/fr_FR/account/equipment/' . $asset->getId());

        self::assertResponseIsSuccessful();
        $link = $crawler->filter('[data-test="equipment-passport-link"]');
        self::assertCount(1, $link, 'The machine page should link to the passport of its equipment model');
        $parts = $crawler->filter('[data-test="equipment-compatible-parts"]')->text();
        self::assertStringContainsString('Jeu de douilles 1/2 pouce 12 pièces', $parts);
        self::assertStringContainsString('OUTI-026', $parts);

        $client->click($link->link());
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Allemagne', $client->getCrawler()->filter('main')->text());
    }

    public function testAVisitorHasNoFleetPage(): void
    {
        $client = self::createClient();

        $client->request('GET', '/fr_FR/account/equipment');

        self::assertResponseRedirects();
    }

    private function login(KernelBrowser $client, string $email): void
    {
        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);

        $client->loginUser($user, 'shop');
    }
}
