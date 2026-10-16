<?php

declare(strict_types=1);

namespace App\Tests\Unit\Theme;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * US-013 — the theme only ADDS hookables (prefix `b2b_`). The single exception is the core logo,
 * which only gets a `condition` (kept on B2C). No plugin hookable is touched.
 */
final class ThemeHooksTest extends TestCase
{
    private const CORE_HOOKABLES_WITH_CONDITION_ONLY = ['logo', 'banner'];

    public function testThemeOnlyAddsHookablesOrConditionsACoreOne(): void
    {
        foreach ($this->hooks() as $hook => $hookables) {
            foreach ($hookables as $name => $config) {
                if (str_starts_with($name, 'b2b_')) {
                    self::assertArrayHasKey('template', $config, "$hook#$name");

                    continue;
                }
                self::assertContains($name, self::CORE_HOOKABLES_WITH_CONDITION_ONLY, "$hook#$name replaces an existing hookable");
                self::assertSame(['condition'], array_keys($config), "$hook#$name may only get a condition");
            }
        }
    }

    public function testNoPluginHookableIsTouched(): void
    {
        foreach ($this->hooks() as $hook => $hookables) {
            foreach (array_keys($hookables) as $name) {
                self::assertStringStartsNotWith('tmonier', $name, "$hook#$name");
            }
        }
    }

    /** @return array<string, array<string, array<string, mixed>>> */
    private function hooks(): array
    {
        $config = Yaml::parseFile(\dirname(__DIR__, 3) . '/config/packages/demo_theme_b2b.yaml');
        self::assertIsArray($config);

        return $config['sylius_twig_hooks']['hooks'];
    }
}
