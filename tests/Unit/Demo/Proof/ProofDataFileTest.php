<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof;

use App\Demo\Proof\ProofFact;
use App\Demo\Proof\ProofReader;
use App\Demo\Proof\Refresh\PluginCatalogFile;
use App\Tests\Support\Typed;
use PHPUnit\Framework\TestCase;

/**
 * US-111: the committed data file (config/demo/proof.json, written by bin/proof-refresh) covers the 24 plugins of the
 * catalogue, every fact it holds is accepted by the reader (nothing would be silently dropped on the page), and it holds
 * no coverage figure and no secret.
 */
final class ProofDataFileTest extends TestCase
{
    private const FILE = __DIR__ . '/../../../../config/demo/proof.json';

    private const CATALOGUE = __DIR__ . '/../../../../config/demo/plugins.yaml';

    public function testTheFileCoversTheCatalogueAndEveryFactIsAcceptedByTheReader(): void
    {
        $data = $this->data();
        $slugs = array_column(PluginCatalogFile::entries(self::CATALOGUE), 'slug');
        sort($slugs);
        $listed = array_keys($data['plugins']);
        sort($listed);
        self::assertSame($slugs, $listed);

        $report = (new ProofReader(self::FILE))->report();
        foreach ($data['plugins'] as $slug => $facts) {
            foreach (array_keys($facts) as $key) {
                self::assertNotSame(ProofFact::UNAVAILABLE, $report->fact($slug, $key)->status, $slug . '.' . $key . ' would be dropped');
            }
        }
        foreach (array_keys($data['delivery']) as $key) {
            self::assertNotSame(ProofFact::UNAVAILABLE, $report->deliveryFact($key)->status, 'delivery.' . $key . ' would be dropped');
        }
    }

    public function testTheFileHoldsNoCoverageFigureAndNoSecret(): void
    {
        $content = (string) file_get_contents(self::FILE);

        foreach ($this->data()['plugins'] as $slug => $facts) {
            self::assertArrayNotHasKey('coverage', $facts, $slug);
        }
        self::assertDoesNotMatchRegularExpression('/gh[pousr]_[A-Za-z0-9]{20,}|github_pat_|Bearer |eyJ[A-Za-z0-9_-]{10,}|access_token/', $content);
        self::assertStringNotContainsString('@', $content);
    }

    /**
     * The demo never claims a plugin is published when Packagist did not know it. The other direction (a catalogue
     * entry still marked unpublished while Packagist has a release) is only a delay of the catalogue, reported by the refresh.
     */
    public function testNoPluginIsPresentedAsPublishedWhenPackagistDoesNotKnowIt(): void
    {
        $report = (new ProofReader(self::FILE))->report();

        foreach (PluginCatalogFile::entries(self::CATALOGUE) as $entry) {
            if ($entry['published']) {
                self::assertSame(ProofFact::VERIFIED, $report->fact($entry['slug'], 'version')->status, $entry['slug'] . ' is published in the catalogue but not on Packagist');
            }
        }
    }

    /** @return array{plugins: array<string, array<string, mixed>>, delivery: array<string, mixed>} */
    private function data(): array
    {
        $data = json_decode((string) file_get_contents(self::FILE), true, 512, \JSON_THROW_ON_ERROR);
        $plugins = [];
        foreach (Typed::map(Typed::at($data, 'plugins'), 'plugins') as $slug => $facts) {
            $plugins[$slug] = Typed::map($facts, 'plugins > ' . $slug);
        }

        return ['plugins' => $plugins, 'delivery' => Typed::map(Typed::at($data, 'delivery'), 'delivery')];
    }
}
