<?php

declare(strict_types=1);

namespace App\Tests\Unit\Theme;

use PHPUnit\Framework\TestCase;

/**
 * US-228: a template of the demo under templates/bundles/ that has the same relative path as a template of the published
 * theme is a second version of the same page. In a channel that uses the theme, the theme wins; the demo keeps its own for
 * the channels without the theme. Each such pair must be listed here with its reason, so a forgotten copy is reported.
 */
final class ThemeTemplateMaskingTest extends TestCase
{
    private const THEME = 'vendor/tmonier/sylius-industry-trade-theme/templates/bundles/';

    private const LOCAL = 'templates/bundles/';

    /** Decided pairs: relative path => why the demo keeps its own version. */
    private const ACCEPTED = [
        'SyliusShopBundle/shared/layout/base.html.twig' => 'Demo layout (landmarks, skip link, measurement): used by every channel. It would be masked by the theme version on any channel that activates the theme.',
        'SyliusShopBundle/shared/layout/base/header/content/security/visitor/desktop/register.html.twig' => 'Demo "Register" link (US-162), used by every channel: hidden when the registration is closed. The theme version (0.3.2) asks RegistrationLinkPolicyInterface, aliased on the same rule (DemoRegistrationLinkPolicy); without this copy a channel without the theme would show a link to a 403.',
        'SyliusShopBundle/shared/components/header/cart.html.twig' => 'Demo cart button (US-077 accessible name, US-192 amount only below xxl on the B2B channel), used by every channel; masked by the theme version (0.3.1) once a channel activates the theme.',
        'SyliusShopBundle/product/index/content/body/main/products.html.twig' => 'Demo product cards, used by every channel; masked by the theme version once a channel activates the theme.',
    ];

    public function testEveryLocalTemplateThatMasksAThemeTemplateIsAccountedFor(): void
    {
        self::assertSame(
            self::sorted(array_keys(self::ACCEPTED)),
            self::masks(),
            'A local template has the same path as a theme template (or an accepted pair no longer exists): review it, then update ACCEPTED.',
        );
    }

    /**
     * @param list<string> $paths
     *
     * @return list<string>
     */
    private static function sorted(array $paths): array
    {
        sort($paths);

        return $paths;
    }

    /** @return list<string> */
    private static function masks(): array
    {
        $root = \dirname(__DIR__, 3) . '/';
        $themeDir = $root . self::THEME;
        self::assertDirectoryExists($themeDir, 'The theme is not installed: run composer install.');

        $masks = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($themeDir, \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            \assert($file instanceof \SplFileInfo);
            $relative = substr($file->getPathname(), \strlen($themeDir));
            if (is_file($root . self::LOCAL . $relative)) {
                $masks[] = $relative;
            }
        }
        sort($masks);

        return $masks;
    }
}
