<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Packs;

use App\Demo\Charter\PublicTextCharter;
use App\Demo\Packs\PackRepositories;
use App\Demo\Packs\PacksReader;
use App\Demo\Proof\Refresh\PluginCatalogFile;
use PHPUnit\Framework\TestCase;

/**
 * US-178: the committed file (config/demo/packs.json, written by bin/packs-refresh) holds both packs, every plugin of a pack
 * is in the catalogue, none is a plugin kept out of the packs (ADR 0015), the text carries the charter, and, when the clones
 * of the pack repositories are at hand, the list of plugins is the one of their composer.json.
 */
final class PacksDataFileTest extends TestCase
{
    private const FILE = __DIR__ . '/../../../../config/demo/packs.json';

    private const CATALOGUE = __DIR__ . '/../../../../config/demo/plugins.yaml';

    /** Plugins classed "concurrent" or "à confirmer" in docs/vitrine-plugins.md, and company-insights, which requires one of them (ADR 0015). */
    private const KEPT_OUT = [
        'sylius-admin-order-creation-plugin',
        'sylius-company-accounts-plugin',
        'sylius-quick-order-plugin',
        'sylius-quote-request-plugin',
        'sylius-purchase-approval-plugin',
        'sylius-tier-pricing-plugin',
        'sylius-withdrawal-plugin',
        'sylius-company-insights-plugin',
    ];

    public function testTheFileHoldsBothPacksAndEverySectionIsAcceptedByTheReader(): void
    {
        $report = new PacksReader(self::FILE)->report();

        self::assertNotNull($report->generatedAt());
        self::assertSame(PackRepositories::ALL, array_map(static fn ($pack): string => $pack->repository, $report->packs()));
        foreach ($report->packs() as $pack) {
            self::assertNotSame([], $pack->plugins, $pack->repository . ' plugins');
            self::assertSame('composer require ' . $pack->composer, $pack->command, $pack->repository);
            $stepped = array_map(static fn ($step): string => $step->slug, $pack->steps);
            $members = $pack->pluginSlugs();
            sort($stepped);
            sort($members);
            self::assertSame($members, $stepped, $pack->repository . ': one step list per plugin of the pack');
            self::assertFalse($pack->stale, $pack->repository . ' was last read from a failed generation');
        }
    }

    public function testEveryPluginOfAPackIsInTheCatalogueAndNoneIsKeptOut(): void
    {
        $catalogue = array_column(PluginCatalogFile::entries(self::CATALOGUE), 'slug');
        $report = new PacksReader(self::FILE)->report();

        self::assertNotSame([], $report->packs());
        foreach ($report->packs() as $pack) {
            self::assertSame([], array_diff($pack->pluginSlugs(), $catalogue), $pack->repository . ' has a plugin outside the catalogue');
            self::assertSame([], array_intersect([...$pack->pluginSlugs(), ...array_map(static fn ($suggestion): string => $suggestion->slug, $pack->suggests)], self::KEPT_OUT), $pack->repository . ' mentions a plugin kept out of the packs');
            self::assertSame([], array_intersect($pack->pluginSlugs(), array_map(static fn ($suggestion): string => $suggestion->slug, $pack->suggests)), 'a suggestion is not a member');
        }
    }

    public function testTheListOfPluginsIsTheOneOfTheComposerJsonOfTheLocalClonesWhenTheyArePresent(): void
    {
        $report = new PacksReader(self::FILE)->report();
        $compared = 0;

        foreach ($report->packs() as $pack) {
            $composer = \dirname(__DIR__, 5) . '/' . $pack->repository . '/composer.json';
            if (!is_file($composer)) {
                continue;
            }
            $decoded = json_decode((string) file_get_contents($composer), true, 512, \JSON_THROW_ON_ERROR);
            $required = \is_array($decoded) && \is_array($decoded['require'] ?? null) ? $decoded['require'] : [];
            $expected = [];
            foreach ($required as $name => $bound) {
                if (str_starts_with((string) $name, 'tmonier/')) {
                    $expected[substr((string) $name, 8)] = $bound;
                }
            }
            $published = [];
            foreach ($pack->plugins as $plugin) {
                $published[$plugin->slug] = $plugin->bound;
            }
            self::assertSame($expected, $published, $pack->repository . ': the page would list other plugins than the composer.json of the pack');
            ++$compared;
        }
        if (0 === $compared) {
            self::markTestSkipped('No clone of the pack repositories next to the demo: the comparison runs where they are.');
        }
    }

    public function testTheTextOfThePacksCarriesTheCharter(): void
    {
        $content = (string) file_get_contents(self::FILE);
        $charter = new PublicTextCharter();

        self::assertSame([], $charter->commercialWording($content));
        self::assertSame([], $charter->ambiguities($content));
        self::assertDoesNotMatchRegularExpression('/\bPlus\b/', $content, 'no mention of a commercial offer');
        self::assertDoesNotMatchRegularExpression('/€|\bprix\b|\bprice\b/iu', $content, 'no price');
    }

    public function testTheFileHoldsNoSecret(): void
    {
        $content = (string) file_get_contents(self::FILE);

        self::assertDoesNotMatchRegularExpression('/gh[pousr]_[A-Za-z0-9]{20,}|github_pat_|Bearer |eyJ[A-Za-z0-9_-]{10,}|access_token/', $content);
    }
}
