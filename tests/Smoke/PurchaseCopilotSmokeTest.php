<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use App\Demo\Channel\DemoChannelContext;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

/**
 * The purchasing copilot (tmonier/sylius-b2b-sales-copilot-plugin, US-244): its widget on the account dashboard of the
 * B2B channel only, and its recorded answers (replay adapter, no model is called in the demo).
 */
final class PurchaseCopilotSmokeTest extends WebTestCase
{
    public function testClaireFindsTheCopilotOnHerDashboard(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $crawler = $client->request('GET', '/fr_FR/account/dashboard');

        self::assertResponseIsSuccessful();
        $widget = $crawler->filter('[data-controller="tmonier-copilot"]');
        self::assertCount(1, $widget, 'The copilot widget should be on the dashboard of the B2B channel');
        self::assertSame('/fr_FR/api/copilot/chat', $widget->attr('data-tmonier-copilot-api-url-value'));
    }

    public function testARecordedRequestGetsItsRecordedAnswer(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $answer = $this->ask($client, 'état de mon panier');

        self::assertStringContainsString('panier', $answer);
    }

    public function testARequestThatWasNotRecordedSaysSo(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $answer = $this->ask($client, 'commande 500 casques pour demain');

        self::assertStringContainsString('Scénario non enregistré en démo', $answer);
    }

    public function testLeaHasNoCopilotOnTheConsumerChannel(): void
    {
        $client = self::createClient();
        $client->getCookieJar()->set(new Cookie(DemoChannelContext::COOKIE, DemoChannelContext::B2C));
        $this->login($client, 'lea@demo.example');

        $crawler = $client->request('GET', '/fr_FR/account/dashboard');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('[data-controller="tmonier-copilot"]'));
    }

    private function ask(KernelBrowser $client, string $message): string
    {
        $client->request('POST', '/fr_FR/api/copilot/chat', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['message' => $message], \JSON_THROW_ON_ERROR));
        self::assertResponseIsSuccessful();
        $body = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($body);
        self::assertIsString($body['message'] ?? null);

        return $body['message'];
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
