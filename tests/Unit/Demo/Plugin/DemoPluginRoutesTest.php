<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Plugin;

use App\Demo\Plugin\DemoPluginCatalog;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouterInterface;

/** US-014 — every demo_route of the plugin catalogue is a real route (no broken demo link). No database needed. */
final class DemoPluginRoutesTest extends KernelTestCase
{
    public function testEveryDemoRouteExistsInTheRouter(): void
    {
        self::bootKernel();
        $catalog = self::getContainer()->get(DemoPluginCatalog::class);
        $router = self::getContainer()->get('router');
        self::assertInstanceOf(DemoPluginCatalog::class, $catalog);
        self::assertInstanceOf(RouterInterface::class, $router);

        $checked = 0;
        foreach ($catalog->all() as $plugin) {
            if (null === $plugin->demoRoute) {
                continue;
            }
            self::assertNotNull($router->getRouteCollection()->get($plugin->demoRoute), \sprintf('Plugin "%s": route "%s" does not exist.', $plugin->slug, $plugin->demoRoute));
            ++$checked;
        }
        self::assertGreaterThan(0, $checked);
    }
}
