<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ReorderBlockStylingTest extends WebTestCase
{
    public function testReorderBlockRendersOnDashboard(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $crawler = $client->request('GET', '/fr_FR/account/dashboard');

        self::assertResponseIsSuccessful();

        $block = $crawler->filter('.reorder-alerts');
        self::assertCount(1, $block, 'Reorder alerts section should be on the dashboard');

        self::assertCount(1, $crawler->filter('.reorder-alerts h2'), 'Section title');

        $alerts = $crawler->filter('[data-test="reorder-alert-item"]');

        if ($alerts->count() > 0) {
            self::assertGreaterThanOrEqual(1, $crawler->filter('.reorder-alert__product')->count());
            self::assertGreaterThanOrEqual(1, $crawler->filter('.reorder-alert__explanation')->count());
            self::assertGreaterThanOrEqual(1, $crawler->filter('.reorder-alert__actions')->count());
        }
    }

    public function testReorderBlockRendersInEnglish(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $crawler = $client->request('GET', '/en_US/account/dashboard');

        self::assertResponseIsSuccessful();

        $block = $crawler->filter('.reorder-alerts');
        self::assertCount(1, $block);
    }

    private function login(KernelBrowser $client, string $email): void
    {
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);

        $client->loginUser($user, 'shop');
    }
}
