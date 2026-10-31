<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CopilotReorderScenarioTest extends WebTestCase
{
    public function testReorderScenarioButtonsAreVisibleInFrench(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $crawler = $client->request('GET', '/fr_FR/account/dashboard');

        self::assertResponseIsSuccessful();

        $widget = $crawler->filter('.copilot-widget');
        self::assertCount(1, $widget, 'Copilot widget should be on the dashboard');

        // Vérifier que les boutons de scénario sont présents
        $suggestions = $crawler->filter('.copilot-widget__suggestions .copilot-widget__chip');
        self::assertGreaterThanOrEqual(3, $suggestions->count(), 'At least 3 scenario buttons should be present');

        // Vérifier que le bouton "recommande comme le mois dernier" est présent
        $reorderButton = $crawler->filter('.copilot-widget__suggestions .copilot-widget__chip:contains("recommande comme le mois dernier")');
        self::assertCount(1, $reorderButton, 'Reorder scenario button should be present');

        // Vérifier que le bouton "recommande avec substitution" est présent
        $reorderSubButton = $crawler->filter('.copilot-widget__suggestions .copilot-widget__chip:contains("recommande avec substitution")');
        self::assertCount(1, $reorderSubButton, 'Reorder with substitution button should be present');
    }

    public function testReorderScenarioButtonsAreVisibleInEnglish(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $crawler = $client->request('GET', '/en_US/account/dashboard');

        self::assertResponseIsSuccessful();

        $widget = $crawler->filter('.copilot-widget');
        self::assertCount(1, $widget, 'Copilot widget should be on the dashboard');

        // Vérifier que les boutons de scénario sont présents
        $suggestions = $crawler->filter('.copilot-widget__suggestions .copilot-widget__chip');
        self::assertGreaterThanOrEqual(3, $suggestions->count(), 'At least 3 scenario buttons should be present');

        // Vérifier que le bouton "reorder like last month" est présent
        $reorderButton = $crawler->filter('.copilot-widget__suggestions .copilot-widget__chip:contains("reorder like last month")');
        self::assertCount(1, $reorderButton, 'Reorder scenario button should be present in English');

        // Vérifier que le bouton "reorder with substitution" est présent
        $reorderSubButton = $crawler->filter('.copilot-widget__suggestions .copilot-widget__chip:contains("reorder with substitution")');
        self::assertCount(1, $reorderSubButton, 'Reorder with substitution button should be present in English');
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
