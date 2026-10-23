<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof\Compatibility\Refresh;

use App\Demo\Proof\Compatibility\Refresh\CompatibilityCollector;
use App\Demo\Proof\Compatibility\Refresh\Fingerprint;
use App\Demo\Proof\ProofReader;
use App\Demo\Proof\Refresh\SourceUnavailable;
use App\Tests\Support\Path;
use App\Tests\Unit\Demo\Proof\Refresh\FakeSources;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

/**
 * US-171: dependencies from the composer.json and tested versions from the CI matrix of a plugin repository, read through
 * the public API only; a repository that cannot be read keeps its last verified values, marked stale with their date.
 */
final class CompatibilityCollectorTest extends TestCase
{
    private const SLUG = 'sylius-trade-credit-plugin';

    private const REPO = 'thibmonier/sylius-trade-credit-plugin';

    private const WORKFLOW = <<<'YAML'
        jobs:
            tests:
                strategy:
                    matrix:
                        php: ["8.3", "8.4"]
                        symfony: ["^7.4"]
                        sylius: ["~2.2.0", "~2.3.0"]
                        database: ["mysql:8.4"]
        YAML;

    public function testRequireAndSuggestAreCollectedApartWithTheirPublicSource(): void
    {
        $facts = $this->collector($this->healthy())->plugin(self::SLUG)->facts;

        self::assertSame(['sylius-order-hold-plugin'], Path::get($facts, 'dependencies.requires'));
        self::assertSame(['sylius-facturx-plugin'], Path::get($facts, 'dependencies.suggests'));
        self::assertSame('https://github.com/' . self::REPO . '/blob/main/composer.json', Path::get($facts, 'dependencies.source'));
        self::assertSame('2026-10-23', Path::get($facts, 'dependencies.checked_at'));
    }

    public function testTheTestedVersionsComeFromTheCiMatrix(): void
    {
        $facts = $this->collector($this->healthy())->plugin(self::SLUG)->facts;

        self::assertSame(['8.3', '8.4'], Path::get($facts, 'ci.php'));
        self::assertSame(['2.2', '2.3'], Path::get($facts, 'ci.sylius'));
        self::assertSame(['7.4'], Path::get($facts, 'ci.symfony'));
        self::assertSame(['mysql:8.4'], Path::get($facts, 'ci.database'));
        self::assertSame('https://github.com/' . self::REPO . '/blob/main/.github/workflows/build.yaml', Path::get($facts, 'ci.source'));
        self::assertSame('2026-10-23', Path::get($facts, 'ci.checked_at'));
    }

    public function testEverySourceIsPublicAndTheFingerprintCoversBothFiles(): void
    {
        $collected = $this->collector($this->healthy())->plugin(self::SLUG);

        self::assertSame([], $collected->failures);
        self::assertTrue(ProofReader::isPublicSource(Path::get($collected->facts, 'dependencies.source')));
        self::assertTrue(ProofReader::isPublicSource(Path::get($collected->facts, 'ci.source')));
        self::assertSame(Fingerprint::of(self::composer(), self::WORKFLOW), Path::get($collected->facts, 'fingerprint'));
    }

    public function testARepositoryWithoutMatrixStillGivesItsDependencies(): void
    {
        $sources = $this->healthy(workflow: "jobs:\n    t:\n        steps:\n            - run: true\n");

        $facts = $this->collector($sources)->plugin(self::SLUG)->facts;

        self::assertSame(['sylius-order-hold-plugin'], Path::get($facts, 'dependencies.requires'));
        self::assertNull(Path::get($facts, 'ci'), 'no matrix, no tested version: nothing is invented');
    }

    public function testAnUnreadableRepositoryKeepsTheLastVerifiedValuesMarkedStaleWithTheirDate(): void
    {
        $previous = [
            'dependencies' => ['requires' => ['sylius-order-hold-plugin'], 'suggests' => [], 'source' => 'https://github.com/' . self::REPO . '/blob/main/composer.json', 'checked_at' => '2026-10-14'],
            'ci' => ['php' => ['8.3'], 'sylius' => ['2.2'], 'symfony' => ['7.4'], 'database' => ['mysql:8.4'], 'source' => 'https://github.com/' . self::REPO . '/blob/main/.github/workflows/build.yaml', 'checked_at' => '2026-10-14'],
            'fingerprint' => 'old',
        ];
        $sources = new FakeSources([], ['repos/' . self::REPO => new SourceUnavailable('rate limit')]);

        $collected = $this->collector($sources)->plugin(self::SLUG, $previous);

        self::assertSame(['github'], $collected->failures);
        self::assertSame(['8.3'], Path::get($collected->facts, 'ci.php'));
        self::assertSame('2026-10-14', Path::get($collected->facts, 'ci.checked_at'), 'the old date, not today');
        self::assertTrue(Path::get($collected->facts, 'ci.stale'));
        self::assertTrue(Path::get($collected->facts, 'dependencies.stale'));
        self::assertSame('old', Path::get($collected->facts, 'fingerprint'), 'the fingerprint of the last verified state, so the check still sees the gap');
    }

    public function testAnUnreadableRepositoryNeverVerifiedBeforeHasNoFact(): void
    {
        $sources = new FakeSources([], ['repos/' . self::REPO => new SourceUnavailable('rate limit')]);

        $collected = $this->collector($sources)->plugin(self::SLUG);

        self::assertSame(['github'], $collected->failures);
        self::assertSame([], $collected->facts);
    }

    public function testAMissingComposerJsonIsAFailureNotAnEmptyDependencyList(): void
    {
        $sources = $this->healthy(composer: null);

        $collected = $this->collector($sources)->plugin(self::SLUG);

        self::assertSame(['github'], $collected->failures);
        self::assertNull(Path::get($collected->facts, 'dependencies'));
    }

    private function collector(FakeSources $sources): CompatibilityCollector
    {
        return new CompatibilityCollector($sources, new MockClock('2026-10-23 10:00:00'));
    }

    private static function composer(): string
    {
        return (string) json_encode([
            'require' => ['php' => '^8.2', 'tmonier/sylius-order-hold-plugin' => '^1.0'],
            'suggest' => ['tmonier/sylius-facturx-plugin' => 'Due date in invoices'],
        ]);
    }

    private function healthy(?string $composer = '', ?string $workflow = null): FakeSources
    {
        return new FakeSources([], [
            'repos/' . self::REPO => ['default_branch' => 'main'],
            'repos/' . self::REPO . '/contents/.github/workflows?ref=main' => [['name' => 'build.yaml', 'type' => 'file']],
        ], [
            self::REPO . ':composer.json' => '' === $composer ? self::composer() : $composer,
            self::REPO . ':.github/workflows/build.yaml' => $workflow ?? self::WORKFLOW,
        ]);
    }
}
