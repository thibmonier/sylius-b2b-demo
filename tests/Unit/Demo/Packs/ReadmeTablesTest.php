<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Packs;

use App\Demo\Packs\ReadmeTables;
use PHPUnit\Framework\TestCase;

/**
 * US-178: what a pack README says, read as tables and not copied by hand: the plugins and their role, the suggested plugin,
 * the steps left to do by hand, in either language of the README (the tables are recognised by their shape, not by a heading).
 */
final class ReadmeTablesTest extends TestCase
{
    public function testTheTitleTheSummaryAndTheInstallCommandAreRead(): void
    {
        $readme = ReadmeTables::parse(PackSamples::readmeEn());

        self::assertSame('AI and headless', $readme['title']);
        self::assertSame('Two Sylius 2 plugins for AI assistants, installed with one `composer require`.', $readme['summary']);
        self::assertSame('composer require tmonier/sylius-pack-ai-headless', $readme['command']);
    }

    public function testTheTitleOfTheFrenchReadmeLosesItsSpaceBeforeTheColon(): void
    {
        self::assertSame('IA et headless', ReadmeTables::parse(PackSamples::readmeFr())['title']);
    }

    public function testThePluginsAreReadWithTheirBoundAndRole(): void
    {
        $plugins = ReadmeTables::parse(PackSamples::readmeEn())['plugins'];

        self::assertSame(
            [
                ['slug' => PackSamples::FEEDS, 'bound' => '^1.0', 'role' => 'Product feeds for AI assistants'],
                ['slug' => PackSamples::OUTBOX, 'bound' => '^1.0', 'role' => 'Outgoing webhooks with retries'],
            ],
            $plugins,
        );
    }

    public function testASuggestedPluginIsNotAMember(): void
    {
        $readme = ReadmeTables::parse(PackSamples::readmeEn());

        self::assertSame([['slug' => PackSamples::AUDIT, 'reason' => 'Records the product change made when a suggestion is accepted.']], $readme['suggests']);
        self::assertNotContains(PackSamples::AUDIT, array_column($readme['plugins'], 'slug'));
    }

    public function testTheStepsLeftByHandAreReadPerPlugin(): void
    {
        $steps = ReadmeTables::parse(PackSamples::readmeEn())['steps'];

        self::assertSame([PackSamples::FEEDS, PackSamples::OUTBOX], array_column($steps, 'slug'));
        self::assertSame(['Tmonier\SyliusAgenticFeedsPlugin\TmonierSyliusAgenticFeedsPlugin'], $steps[0]['bundles']);
        self::assertSame(['@TmonierSyliusAgenticFeedsPlugin/config/config.yaml'], $steps[0]['config']);
        self::assertSame(['@TmonierSyliusAgenticFeedsPlugin/config/routes.yaml'], $steps[0]['routes']);
        self::assertTrue($steps[0]['migrations']);
        self::assertFalse($steps[1]['migrations']);
        self::assertSame('schedule `tmonier:feeds:generate` (cron)', $steps[0]['other']);
    }

    public function testTheFrenchReadmeGivesTheSameStepsWithItsOwnWords(): void
    {
        $english = ReadmeTables::parse(PackSamples::readmeEn())['steps'];
        $french = ReadmeTables::parse(PackSamples::readmeFr())['steps'];

        self::assertSame(array_column($english, 'slug'), array_column($french, 'slug'));
        self::assertSame(array_column($english, 'bundles'), array_column($french, 'bundles'));
        self::assertSame(array_column($english, 'migrations'), array_column($french, 'migrations'), '"oui" and "non" read like "yes" and "no"');
        self::assertSame('planifier `tmonier:feeds:generate` (cron)', $french[0]['other']);
    }

    public function testSeveralBundlesAndImportsInOneCellAreAllKept(): void
    {
        $readme = "# Sylius Pack: X\n\n| Plugin | Bundles | Configuration | Routes | Migrations | Other |\n|---|---|---|---|---|---|\n"
            . '| b2b-mcp | `A\One`, `B\Two` and `C\Three` | `@A/config.yaml` and `@B/config.yaml` | `@A/routes.yaml` and `@B/routes.yaml` | yes (2 tables) | none |' . "\n";

        $step = ReadmeTables::parse($readme)['steps'][0];

        self::assertSame(['A\One', 'B\Two', 'C\Three'], $step['bundles']);
        self::assertSame(['@A/config.yaml', '@B/config.yaml'], $step['config']);
        self::assertSame(['@A/routes.yaml', '@B/routes.yaml'], $step['routes']);
        self::assertTrue($step['migrations'], '"yes (2 tables)" is a yes');
    }

    public function testAReadmeWithoutTablesGivesNothingInvented(): void
    {
        $readme = ReadmeTables::parse("# Sylius Pack: Empty\n\nNo table here.\n");

        self::assertSame('Empty', $readme['title']);
        self::assertSame([], $readme['plugins']);
        self::assertSame([], $readme['suggests']);
        self::assertSame([], $readme['steps']);
        self::assertNull($readme['command']);
    }
}
