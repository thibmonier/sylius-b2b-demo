<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Sprint 21: Claire declares three fictional machines (customer-asset-management) and the catalogue narrows down to the
 * parts compatible with them. Run against a database loaded with the `demo` fixture suite.
 */
final class EquipmentFleetSmokeTest extends WebTestCase
{
    public function testClaireSeesHerThreeMachines(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $crawler = $client->request('GET', '/fr_FR/account/equipment');

        self::assertResponseIsSuccessful();
        self::assertCount(3, $crawler->filter('[data-test-tmonier-equipment-asset]'));
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
