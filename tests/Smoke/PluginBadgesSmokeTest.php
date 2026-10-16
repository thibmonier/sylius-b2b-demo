<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use App\Demo\Badge\BadgePreference;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

/** US-016 — plugin badges in the shop, the banner switch and its persistence. */
final class PluginBadgesSmokeTest extends WebTestCase
{
    public function testTheCartPageShowsPluginBadgesLinkingToThePluginsPage(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');
        $crawler = $client->request('GET', '/fr_FR/cart/');

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, $crawler->filter('[data-demo-plugin-badge="sylius-quick-order-plugin"] a[href*="/plugins#plugin-sylius-quick-order-plugin"]')->count());
    }

    public function testTheBannerSwitchHidesTheBadgesAndKeepsThePreferenceAcrossPages(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');
        $client->request('GET', '/demo/badges/off', server: ['HTTP_REFERER' => 'http://localhost/fr_FR/cart/']);
        self::assertResponseRedirects('/fr_FR/cart/');

        foreach (['/fr_FR/cart/', '/fr_FR/plugins'] as $path) {
            $crawler = $client->request('GET', $path);
            self::assertCount(0, $crawler->filter('[data-demo-plugin-badge]'), $path);
            self::assertSame('false', $crawler->filter('[data-demo-badges-slot] [data-demo-badges-toggle]')->attr('aria-pressed'), $path);
        }

        $client->request('GET', '/demo/badges/on');
        $crawler = $client->request('GET', '/fr_FR/cart/');
        self::assertGreaterThan(0, $crawler->filter('[data-demo-plugin-badge]')->count());
    }

    public function testTheCookieAloneControlsTheBadges(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');
        $client->getCookieJar()->set(new Cookie(BadgePreference::COOKIE, BadgePreference::HIDDEN));
        $crawler = $client->request('GET', '/fr_FR/cart/');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('[data-demo-plugin-badge]'));
    }

    /** The B2B channel hides its prices and its cart from visitors who are not signed in (US-019): sign in first. */
    private function login(KernelBrowser $client, string $email): void
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);

        $client->loginUser($user, 'shop');
    }
}
