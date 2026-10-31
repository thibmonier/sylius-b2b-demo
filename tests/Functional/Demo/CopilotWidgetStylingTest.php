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

        self::assertCount(1, $crawler->filter('.copilot-widget h3'), 'Widget title should be present');
        self::assertCount(1, $crawler->filter('.copilot-widget__conversation[aria-live="polite"]'), 'Conversation area with aria-live should be present');

        self::assertCount(1, $crawler->filter('.copilot-widget input.copilot-widget__input'), 'Input field should be present');
        self::assertCount(1, $crawler->filter('.copilot-widget button[type="submit"]'), 'Send button should be present');
    }

    public function testWidgetRendersInEnglishLocale(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $crawler = $client->request('GET', '/en_US/account/dashboard');

        self::assertResponseIsSuccessful();

        $widget = $crawler->filter('.copilot-widget');
        self::assertCount(1, $widget);

        $title = $crawler->filter('.copilot-widget h3');
        self::assertCount(1, $title, 'Widget title should be present in English locale');
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
