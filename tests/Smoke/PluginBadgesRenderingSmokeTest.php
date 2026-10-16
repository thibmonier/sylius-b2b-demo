<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

/**
 * Badges are really rendered (regression: the template read a `hookable_configuration` variable
 * that Sylius twig hooks never expose, so no badge was ever displayed while the hook registry
 * tests stayed green). Requires a database loaded with the `demo` fixture suite.
 */
final class PluginBadgesRenderingSmokeTest extends WebTestCase
{
    private const PRODUCT_PAGE = '/fr_FR/products/epi-cask-001-fr';

    public function testTheProductPageRendersPluginBadges(): void
    {
        $client = self::createClient();
        $client->request('GET', self::PRODUCT_PAGE);

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, $client->getCrawler()->filter('[data-demo-plugin-badge]')->count());
    }

    public function testTheBadgesAreHiddenOnceSwitchedOff(): void
    {
        $client = self::createClient();
        $client->getCookieJar()->set(new Cookie('demo_badges', 'off'));
        $client->request('GET', self::PRODUCT_PAGE);

        self::assertResponseIsSuccessful();
        self::assertSame(0, $client->getCrawler()->filter('[data-demo-plugin-badge]')->count());
    }
}
