<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Packs;

use App\Demo\Packs\PackCollector;
use App\Demo\Packs\PackFingerprint;
use App\Demo\Proof\ProofReader;
use App\Demo\Proof\Refresh\SourceUnavailable;
use App\Tests\Support\Path;
use App\Tests\Unit\Demo\Proof\Refresh\FakeSources;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

/**
 * US-178: the facts of a pack (plugins, install command, steps left by hand) read from its composer.json and its two READMEs
 * through the public API only; a repository that cannot be read keeps its last verified values, marked stale, with their date.
 */
final class PackCollectorTest extends TestCase
{
    private const REPO = 'thibmonier/sylius-pack-ai-headless';

    public function testThePluginsAndTheirBoundsComeFromTheComposerJsonAndTheRolesFromTheReadmes(): void
    {
        $facts = $this->collector($this->healthy())->pack(PackSamples::REPOSITORY)->facts;

        self::assertSame('tmonier/sylius-pack-ai-headless', Path::get($facts, 'composer'));
        self::assertSame(PackSamples::pack()['plugins'], Path::get($facts, 'plugins'));
        self::assertSame(PackSamples::pack()['suggests'], Path::get($facts, 'suggests'));
    }

    public function testTheInstallCommandTheTitleAndTheStepsComeFromTheReadmes(): void
    {
        $facts = $this->collector($this->healthy())->pack(PackSamples::REPOSITORY)->facts;

        self::assertSame('composer require tmonier/sylius-pack-ai-headless', Path::get($facts, 'command'));
        self::assertSame(['en' => 'AI and headless', 'fr' => 'IA et headless'], Path::get($facts, 'title'));
        self::assertSame(PackSamples::pack()['steps'], Path::get($facts, 'steps'));
    }

    public function testEverySourceIsPublicAndTheFingerprintCoversTheThreeFiles(): void
    {
        $collected = $this->collector($this->healthy())->pack(PackSamples::REPOSITORY);

        self::assertSame([], $collected->failures);
        self::assertSame('https://github.com/' . self::REPO . '/blob/main/README.md', Path::get($collected->facts, 'source'));
        self::assertSame('https://github.com/' . self::REPO . '/blob/main/composer.json', Path::get($collected->facts, 'composer_source'));
        self::assertTrue(ProofReader::isPublicSource(Path::get($collected->facts, 'source')));
        self::assertTrue(ProofReader::isPublicSource(Path::get($collected->facts, 'composer_source')));
        self::assertSame('2026-10-24', Path::get($collected->facts, 'checked_at'));
        self::assertSame(PackFingerprint::of(PackSamples::composer(), PackSamples::readmeEn(), PackSamples::readmeFr()), Path::get($collected->facts, 'fingerprint'));
    }

    public function testAReadmeThatDisagreesWithTheComposerJsonIsAFailureNotASilentChoice(): void
    {
        $composer = (string) json_encode(['name' => 'tmonier/sylius-pack-ai-headless', 'require' => ['tmonier/sylius-agentic-feeds-plugin' => '^1.0', 'tmonier/sylius-gpsr-plugin' => '^1.0']]);

        $collected = $this->collector($this->healthy(composer: $composer))->pack(PackSamples::REPOSITORY);

        self::assertSame(['github'], $collected->failures);
        self::assertNull(Path::get($collected->facts, 'plugins'), 'the list of the README is not shown next to another composer.json');
    }

    public function testAMissingFrenchReadmeIsAFailure(): void
    {
        $collected = $this->collector($this->healthy(readmeFr: null))->pack(PackSamples::REPOSITORY);

        self::assertSame(['github'], $collected->failures);
    }

    public function testAnUnreadableRepositoryKeepsTheLastVerifiedValuesMarkedStaleWithTheirDate(): void
    {
        $previous = [...PackSamples::pack(), 'checked_at' => '2026-10-14', 'fingerprint' => 'old'];
        $sources = new FakeSources([], ['repos/' . self::REPO => new SourceUnavailable('rate limit')]);

        $collected = $this->collector($sources)->pack(PackSamples::REPOSITORY, $previous);

        self::assertSame(['github'], $collected->failures);
        self::assertSame(PackSamples::pack()['plugins'], Path::get($collected->facts, 'plugins'));
        self::assertSame('2026-10-14', Path::get($collected->facts, 'checked_at'), 'the old date, not today');
        self::assertTrue(Path::get($collected->facts, 'stale'));
        self::assertSame('old', Path::get($collected->facts, 'fingerprint'), 'the fingerprint of the last verified state, so the check still sees the gap');
    }

    public function testAnUnreadableRepositoryNeverVerifiedBeforeHasNoFact(): void
    {
        $sources = new FakeSources([], ['repos/' . self::REPO => new SourceUnavailable('rate limit')]);

        $collected = $this->collector($sources)->pack(PackSamples::REPOSITORY);

        self::assertSame(['github'], $collected->failures);
        self::assertSame([], $collected->facts);
    }

    private function collector(FakeSources $sources): PackCollector
    {
        return new PackCollector($sources, new MockClock('2026-10-24 10:00:00'));
    }

    private function healthy(?string $composer = '', ?string $readmeFr = ''): FakeSources
    {
        return new FakeSources([], [
            'repos/' . self::REPO => ['default_branch' => 'main'],
        ], [
            self::REPO . ':composer.json' => '' === $composer ? PackSamples::composer() : $composer,
            self::REPO . ':README.md' => PackSamples::readmeEn(),
            self::REPO . ':README.fr.md' => '' === $readmeFr ? PackSamples::readmeFr() : $readmeFr,
        ]);
    }
}
