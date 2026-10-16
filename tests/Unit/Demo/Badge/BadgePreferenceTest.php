<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Badge;

use App\Demo\Badge\BadgePreference;
use App\Demo\Controller\BadgeToggleController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/** US-016 — badges are shown by default in demo mode, hidden by cookie, and the switch is safe. */
final class BadgePreferenceTest extends TestCase
{
    public function testBadgesAreVisibleByDefaultInDemoMode(): void
    {
        self::assertTrue(self::preference(new Request(), true)->visible());
    }

    public function testBadgesAreHiddenOnceSwitchedOff(): void
    {
        $request = new Request(cookies: [BadgePreference::COOKIE => BadgePreference::HIDDEN]);

        self::assertFalse(self::preference($request, true)->visible());
    }

    public function testBadgesComeBackWhenSwitchedOnAgain(): void
    {
        self::assertTrue(self::preference(new Request(cookies: [BadgePreference::COOKIE => 'on']), true)->visible());
    }

    public function testBadgesNeverShowOutsideDemoMode(): void
    {
        self::assertFalse(self::preference(new Request(), false)->visible());
    }

    public function testTheSwitchSetsTheCookieAndGoesBackToTheSamePage(): void
    {
        $request = Request::create('http://shop.test/demo/badges/off', server: ['HTTP_REFERER' => 'http://shop.test/fr_FR/cart?a=1']);

        $response = self::controller()($request, 'off');

        self::assertSame('/fr_FR/cart?a=1', $response->getTargetUrl());
        $cookie = $response->headers->getCookies()[0];
        self::assertSame(BadgePreference::COOKIE, $cookie->getName());
        self::assertSame(BadgePreference::HIDDEN, $cookie->getValue());
    }

    public function testTheSwitchNeverRedirectsToAnotherHost(): void
    {
        $request = Request::create('http://shop.test/demo/badges/on', server: ['HTTP_REFERER' => 'https://evil.example/phish']);

        $response = self::controller()($request, 'on');

        self::assertSame('/home', $response->getTargetUrl());
        self::assertSame('on', $response->headers->getCookies()[0]->getValue());
    }

    private static function preference(Request $request, bool $demoMode): BadgePreference
    {
        $stack = new RequestStack();
        $stack->push($request);

        return new BadgePreference($stack, $demoMode);
    }

    private static function controller(): BadgeToggleController
    {
        $urls = self::createStub(UrlGeneratorInterface::class);
        $urls->method('generate')->willReturn('/home');

        return new BadgeToggleController($urls);
    }
}
