<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CopilotWidgetStylingTest extends WebTestCase
{
    public function testWidgetHasStyledStructureOnDashboard(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $crawler = $client->request('GET', '/fr_FR/account/dashboard');

        self::assertResponseIsSuccessful();

        $widget = $crawler->filter('.copilot-widget');
        self::assertCount(1, $widget, 'Styled copilot widget should be on the dashboard');

        self::assertCount(1, $crawler->filter('.copilot-widget__header'), 'Widget header should be present');
        self::assertCount(1, $crawler->filter('.copilot-widget__conversation[aria-live="polite"]'), 'Conversation with aria-live should be present');
        self::assertCount(1, $crawler->filter('.copilot-widget__empty'), 'Empty state should be present');
        self::assertCount(1, $crawler->filter('.copilot-widget__suggestions'), 'Suggestions should be present');

        $chips = $crawler->filter('.copilot-widget__chip');
        self::assertGreaterThanOrEqual(2, $chips->count(), 'At least 2 suggestion chips');

        self::assertCount(1, $crawler->filter('.copilot-widget__input'), 'Input field should be present');
        self::assertCount(1, $crawler->filter('.copilot-widget__send[aria-label]'), 'Send button with aria-label');
        self::assertCount(1, $crawler->filter('.copilot-widget__notice'), 'Demo notice should be present');
    }

    public function testWidgetRendersInEnglishLocale(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $crawler = $client->request('GET', '/en_US/account/dashboard');

        self::assertResponseIsSuccessful();

        $widget = $crawler->filter('.copilot-widget');
        self::assertCount(1, $widget);

        $notice = $crawler->filter('.copilot-widget__notice');
        self::assertStringContainsString('Pre-recorded', $notice->text());
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
