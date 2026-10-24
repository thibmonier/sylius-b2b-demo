<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Einvoice;

use App\Demo\Einvoice\FictitiousPlatformTransmitter;
use App\Tests\Support\Typed;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/** US-183: the demo installs the transmission plugin from Packagist and plugs the fictitious platform into it. */
final class EinvoiceTransmissionConfigurationTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../../..';

    public function testTheAdapterIsChosenByServiceAndTheDemoStatusesAreDeclared(): void
    {
        $config = Yaml::parseFile(self::ROOT . '/config/packages/tmonier_einvoice_transmission.yaml');

        self::assertSame(FictitiousPlatformTransmitter::class, Typed::at($config, 'tmonier_sylius_einvoice_transmission', 'transmitter'));
        self::assertSame(['refused', 'collected'], Typed::at($config, 'tmonier_sylius_einvoice_transmission', 'additional_statuses'));
    }

    public function testThePluginComesFromPackagistAtTheReleasedMinorVersion(): void
    {
        $composer = json_decode((string) file_get_contents(self::ROOT . '/composer.json'), true, 512, \JSON_THROW_ON_ERROR);

        self::assertSame('^0.2', Typed::at($composer, 'require', 'tmonier/sylius-einvoice-transmission-plugin'));
        self::assertArrayNotHasKey('repositories', \is_array($composer) ? $composer : [], 'no VCS repository as a workaround');
        self::assertStringContainsString('TmonierSyliusEinvoiceTransmissionPlugin::class', (string) file_get_contents(self::ROOT . '/config/bundles.php'));
    }

    public function testThePluginIsInTheCatalogueWithBothLanguagesAndNoRealPlatform(): void
    {
        $catalogue = (string) file_get_contents(self::ROOT . '/config/demo/plugins.yaml');

        self::assertStringContainsString('slug: sylius-einvoice-transmission-plugin', $catalogue);
        self::assertStringContainsString('demo_route: app_demo_fictitious_platform', $catalogue, 'the entry points to the page of the fictitious platform');
    }
}
