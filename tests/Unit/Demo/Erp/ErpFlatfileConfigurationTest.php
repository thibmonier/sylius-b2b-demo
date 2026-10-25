<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Erp;

use App\Tests\Support\Typed;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/** US-190: the demo installs the flat-file plugin from Packagist, after the webhook outbox it reads, and lists it in the catalogue. */
final class ErpFlatfileConfigurationTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../../..';

    public function testThePluginComesFromPackagistAtTheReleasedMinorVersion(): void
    {
        $composer = json_decode((string) file_get_contents(self::ROOT . '/composer.json'), true, 512, \JSON_THROW_ON_ERROR);

        self::assertSame('^0.2', Typed::at($composer, 'require', 'tmonier/sylius-erp-flatfile-plugin'));
        self::assertArrayNotHasKey('repositories', \is_array($composer) ? $composer : [], 'no VCS repository as a workaround');
    }

    public function testTheBundleIsRegisteredAfterTheWebhookOutboxItReadsFrom(): void
    {
        $bundles = (string) file_get_contents(self::ROOT . '/config/bundles.php');

        $outbox = strpos($bundles, 'TmonierSyliusWebhookOutboxPlugin::class');
        $flatfile = strpos($bundles, 'TmonierSyliusErpFlatfilePlugin::class');
        self::assertNotFalse($outbox);
        self::assertNotFalse($flatfile);
        self::assertGreaterThan($outbox, $flatfile);
    }

    public function testTheExchangeDirectoryIsUnderVarAndNotPublic(): void
    {
        $config = Yaml::parseFile(self::ROOT . '/config/packages/tmonier_erp_flatfile.yaml');

        $directory = Typed::at($config, 'tmonier_sylius_erp_flatfile', 'exchange_directory');
        self::assertIsString($directory);
        self::assertStringStartsWith('%kernel.project_dir%/var/', $directory);
        self::assertStringNotContainsString('public', $directory);
    }

    public function testThePluginIsInTheCatalogueAndPointsToItsPage(): void
    {
        $catalogue = (string) file_get_contents(self::ROOT . '/config/demo/plugins.yaml');

        self::assertStringContainsString('slug: sylius-erp-flatfile-plugin', $catalogue);
        self::assertStringContainsString('demo_route: app_demo_erp_flatfile', $catalogue);
    }
}
