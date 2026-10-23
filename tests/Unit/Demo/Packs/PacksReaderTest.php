<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Packs;

use App\Demo\Packs\PacksReader;
use App\Tests\Support\Path;
use PHPUnit\Framework\TestCase;

/**
 * US-178: reads config/demo/packs.json. A pack without a public source or a check date is dropped; a missing or broken file
 * is an empty report, never an error. No network call, ever.
 */
final class PacksReaderTest extends TestCase
{
    /** @var list<string> */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }
    }

    public function testEveryFieldOfAPackIsRead(): void
    {
        $pack = $this->reader(PackSamples::data())->report()->pack(PackSamples::REPOSITORY);

        self::assertNotNull($pack);
        self::assertSame('tmonier/sylius-pack-ai-headless', $pack->composer);
        self::assertSame('composer require tmonier/sylius-pack-ai-headless', $pack->command);
        self::assertSame('AI and headless', $pack->title('en_US'));
        self::assertSame('IA et headless', $pack->title('fr_FR'));
        self::assertSame([PackSamples::FEEDS, PackSamples::OUTBOX], $pack->pluginSlugs());
        self::assertSame('^1.0', $pack->plugins[0]->bound);
        self::assertSame('Flux produits pour les assistants IA', $pack->plugins[0]->role('fr_FR'));
        self::assertSame('Product feeds for AI assistants', $pack->plugins[0]->role('en_US'));
        self::assertSame(PackSamples::AUDIT, $pack->suggests[0]->slug);
        self::assertTrue($pack->steps[0]->migrations);
        self::assertSame('2026-10-24', $pack->checkedAt);
        self::assertFalse($pack->stale);
    }

    public function testTheTotalsAreComputedFromTheStepsAndNotStored(): void
    {
        $pack = $this->reader(PackSamples::data())->report()->pack(PackSamples::REPOSITORY);

        self::assertNotNull($pack);
        self::assertSame(['bundles' => 2, 'config' => 2, 'routes' => 2, 'migrations' => 1], $pack->totals());
    }

    public function testABundleDeclaredByTwoPluginsIsCountedOnce(): void
    {
        $data = Path::with(PackSamples::data(), 'packs.' . PackSamples::REPOSITORY . '.steps.1.bundles', ['Tmonier\SyliusAgenticFeedsPlugin\TmonierSyliusAgenticFeedsPlugin']);

        $pack = $this->reader($data)->report()->pack(PackSamples::REPOSITORY);

        self::assertNotNull($pack);
        self::assertSame(1, $pack->totals()['bundles']);
    }

    public function testALastValueKeptAfterAFailedGenerationKeepsItsStaleMark(): void
    {
        $data = Path::with(PackSamples::data(), 'packs.' . PackSamples::REPOSITORY . '.stale', true);

        $pack = $this->reader($data)->report()->pack(PackSamples::REPOSITORY);

        self::assertNotNull($pack);
        self::assertTrue($pack->stale);
    }

    public function testAPackWithoutAPublicSourceIsDropped(): void
    {
        $data = Path::with(PackSamples::data(), 'packs.' . PackSamples::REPOSITORY . '.source', 'https://intranet.example/README.md');

        $report = $this->reader($data)->report();

        self::assertNull($report->pack(PackSamples::REPOSITORY));
        self::assertNotNull($report->pack(PackSamples::OTHER_REPOSITORY));
    }

    public function testAPackWithoutACheckDateIsDropped(): void
    {
        $data = Path::with(PackSamples::data(), 'packs.' . PackSamples::REPOSITORY . '.checked_at', 'yesterday');

        self::assertNull($this->reader($data)->report()->pack(PackSamples::REPOSITORY));
    }

    public function testAPackWithoutPluginsIsDropped(): void
    {
        $data = Path::with(PackSamples::data(), 'packs.' . PackSamples::REPOSITORY . '.plugins', []);

        self::assertNull($this->reader($data)->report()->pack(PackSamples::REPOSITORY));
    }

    public function testAMissingOrBrokenFileIsAnEmptyReport(): void
    {
        foreach ([new PacksReader(sys_get_temp_dir() . '/packs-missing-' . bin2hex(random_bytes(4)) . '.json'), new PacksReader($this->file('{ not json'))] as $reader) {
            self::assertSame([], $reader->report()->packs());
            self::assertNull($reader->report()->generatedAt());
        }
    }

    /** @param array<array-key, mixed> $data */
    private function reader(array $data): PacksReader
    {
        return new PacksReader($this->file((string) json_encode($data, \JSON_THROW_ON_ERROR)));
    }

    private function file(string $content): string
    {
        $file = tempnam(sys_get_temp_dir(), 'packs-reader') ?: throw new \RuntimeException('no temporary file');
        file_put_contents($file, $content);

        return $this->files[] = $file;
    }
}
