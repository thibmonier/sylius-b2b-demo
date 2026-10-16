<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Badge;

use App\Demo\Badge\BadgePreference;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

/** US-016 — the badge template: accessible link to the plugin card, hidden when switched off, silent for unknown plugins. */
final class BadgeTemplateTest extends KernelTestCase
{
    public function testTheBadgeLinksToThePluginCardWithAnAccessibleTooltip(): void
    {
        $html = $this->render(new Request(attributes: ['_locale' => 'en_US']), 'sylius-quote-request-plugin');

        self::assertStringContainsString('data-demo-plugin-badge="sylius-quote-request-plugin"', $html);
        self::assertStringContainsString('href="/en_US/plugins#plugin-sylius-quote-request-plugin"', $html);
        self::assertStringContainsString('title="Plugin tmonier/sylius-quote-request-plugin"', $html);
        self::assertStringContainsString('aria-label="Plugin tmonier/sylius-quote-request-plugin"', $html);
        self::assertStringContainsString('powered by Quote Request', $html);
    }

    public function testNothingIsRenderedOnceBadgesAreSwitchedOff(): void
    {
        $request = new Request(cookies: [BadgePreference::COOKIE => BadgePreference::HIDDEN], attributes: ['_locale' => 'en_US']);

        self::assertSame('', trim($this->render($request, 'sylius-quote-request-plugin')));
    }

    public function testAnUnknownPluginRendersNothingWithoutError(): void
    {
        self::assertSame('', trim($this->render(new Request(attributes: ['_locale' => 'en_US']), 'sylius-not-a-plugin')));
    }

    private function render(Request $request, string $slug): string
    {
        self::bootKernel();
        $container = self::getContainer();
        $stack = $container->get(RequestStack::class);
        $twig = $container->get(Environment::class);
        self::assertInstanceOf(RequestStack::class, $stack);
        self::assertInstanceOf(Environment::class, $twig);
        $request->setLocale('en_US');
        $stack->push($request);

        return $twig->render('demo/plugin_badge.html.twig', ['hookable_metadata' => ['configuration' => ['plugin' => $slug]]]);
    }
}
