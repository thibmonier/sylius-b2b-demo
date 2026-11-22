<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

/**
 * T-TECH-56-02: on a channel that uses the published theme, its "Register" link template wins over the one of the application
 * (0.3.2). The theme asks the application whether the link is visible: the demo answers with its own rule (US-162), the one
 * of the 403 on the registration routes.
 */
final class ThemeRegistrationLinkTest extends KernelTestCase
{
    use EnvironmentSwitch;

    private const THEME_TEMPLATE = 'vendor/tmonier/sylius-industry-trade-theme/templates/bundles/SyliusShopBundle/shared/layout/base/header/content/security/visitor/desktop/register.html.twig';

    public function testAClosedRegistrationHidesTheThemeLink(): void
    {
        $this->setEnvironment(['DEMO_REGISTRATION_OPEN' => '0']);

        self::assertStringNotContainsString('register-page-button', $this->renderThemeLink());
    }

    public function testAnOpenRegistrationShowsTheThemeLink(): void
    {
        $this->setEnvironment(['DEMO_REGISTRATION_OPEN' => '1']);

        self::assertStringContainsString('register-page-button', $this->renderThemeLink());
    }

    private function renderThemeLink(): string
    {
        self::bootKernel();
        $container = self::getContainer();
        $request = Request::create('/fr_FR/');
        $request->attributes->set('_locale', 'fr_FR');
        $requestStack = $container->get(RequestStack::class);
        self::assertInstanceOf(RequestStack::class, $requestStack);
        $requestStack->push($request);

        $source = file_get_contents(\dirname(__DIR__, 3) . '/' . self::THEME_TEMPLATE);
        self::assertIsString($source);
        $twig = $container->get(Environment::class);
        self::assertInstanceOf(Environment::class, $twig);

        return $twig->createTemplate($source)->render([]);
    }
}
