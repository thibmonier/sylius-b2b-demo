<?php

declare(strict_types=1);

namespace App\Tests\Unit\Theme;

use App\Tests\Support\Typed;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * US-013 — the theme only ADDS hookables (prefix `b2b_`). The single exception is the core logo,
 * which only gets a `condition` (kept on B2C). No plugin hookable is touched. US-144: the core
 * newsletter offer of the top bar is the only core hookable switched off.
 */
final class ThemeHooksTest extends TestCase
{
    private const CORE_HOOKABLES_WITH_CONDITION_ONLY = ['logo', 'banner', 'mobile', 'desktop'];

    private const CORE_HOOKABLES_DISABLED = ['sylius_shop.base.header.top_bar' => ['info_message']];

    public function testThemeOnlyAddsHookablesOrConditionsACoreOne(): void
    {
        foreach ($this->hooks() as $hook => $hookables) {
            foreach ($hookables as $name => $config) {
                if (str_starts_with($name, 'b2b_')) {
                    self::assertArrayHasKey('template', $config, "$hook#$name");

                    continue;
                }
                if (\in_array($name, self::CORE_HOOKABLES_DISABLED[$hook] ?? [], true)) {
                    self::assertSame(['enabled' => false], $config, "$hook#$name may only be switched off");

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
        $hooks = [];
        foreach (Typed::map(Typed::at($config, 'sylius_twig_hooks', 'hooks'), 'hooks') as $hook => $entries) {
            foreach (Typed::map($entries, $hook) as $name => $entry) {
                $hooks[$hook][$name] = Typed::map($entry, $hook . ' > ' . $name);
            }
        }

        return $hooks;
    }
}
