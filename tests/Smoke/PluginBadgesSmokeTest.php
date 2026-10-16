<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use App\Demo\Badge\BadgePreference;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

/** US-016 — plugin badges in the shop, the banner switch and its persistence. */
final class PluginBadgesSmokeTest extends WebTestCase
{
    public function testTheCartPageShowsPluginBadgesLinkingToThePluginsPage(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/fr_FR/cart/');

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, $crawler->filter('[data-demo-plugin-badge="sylius-quick-order-plugin"] a[href*="/plugins#plugin-sylius-quick-order-plugin"]')->count());
    }

    public function testTheBannerSwitchHidesTheBadgesAndKeepsThePreferenceAcrossPages(): void
    {
        $client = self::createClient();
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
        $client->getCookieJar()->set(new Cookie(BadgePreference::COOKIE, BadgePreference::HIDDEN));
        $crawler = $client->request('GET', '/fr_FR/cart/');

        self::assertCount(0, $crawler->filter('[data-demo-plugin-badge]'));
    }
}
