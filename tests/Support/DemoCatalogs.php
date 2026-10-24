<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Demo\Account\DemoAccountCatalog;
use App\Demo\Plugin\DemoPluginCatalog;
use Symfony\Component\Yaml\Yaml;

/** The demo account and plugin catalogues built from config/demo/*.yaml, without booting the kernel (US-174). */
final class DemoCatalogs
{
    public static function accounts(): DemoAccountCatalog
    {
        $parameters = Typed::at(Yaml::parseFile(self::root() . '/config/demo/accounts.yaml'), 'parameters');

        return new DemoAccountCatalog(
            Typed::string(Typed::at($parameters, 'demo.password'), 'demo.password'),
            Typed::listOfMaps(Typed::at($parameters, 'demo.companies'), 'demo.companies'),
            Typed::listOfMaps(Typed::at($parameters, 'demo.consumers'), 'demo.consumers'),
            Typed::listOfMaps(Typed::at($parameters, 'demo.admin_users'), 'demo.admin_users'),
        );
    }

    public static function plugins(): DemoPluginCatalog
    {
        $entries = Typed::at(Yaml::parseFile(self::root() . '/config/demo/plugins.yaml'), 'parameters', 'demo.plugin_catalog');

        return new DemoPluginCatalog(Typed::listOfMaps($entries, 'demo.plugin_catalog'), self::accounts());
    }

    /** The number of plugins of the catalogue, counted in config/demo/plugins.yaml: the expected value of every test that counts them. */
    public static function pluginCount(): int
    {
        $entries = Typed::at(Yaml::parseFile(self::root() . '/config/demo/plugins.yaml'), 'parameters', 'demo.plugin_catalog');

        return \is_array($entries) ? \count($entries) : 0;
    }

    private static function root(): string
    {
        return \dirname(__DIR__, 2);
    }
}
