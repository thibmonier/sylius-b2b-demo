<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof\Refresh;

use App\Demo\Proof\ProofReader;
use App\Demo\Proof\Refresh\ProofCollector;
use App\Demo\Proof\Refresh\SourceUnavailable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

/**
 * US-111: the refresh collects only what a public source states (Packagist, the GitHub REST API), keeps the last
 * verified value (marked stale) when a source fails, and never invents a figure: no coverage is ever written.
 */
final class ProofCollectorTest extends TestCase
{
    private const SLUG = 'sylius-quick-order-plugin';

    private const REPO = 'thibmonier/sylius-quick-order-plugin';

    private const WORKFLOW = <<<'YAML'
        name: Build
        jobs:
            tests:
                strategy:
                    matrix:
                        php: ["8.3", "8.4"]
                        sylius: ["~2.2.0", "~2.3.0"]
                        include:
                            - php: "8.4"
                              sylius: "~2.3.0"
                steps:
                    - run: vendor/bin/phpstan analyse --no-progress
                    - run: vendor/bin/ecs check
        YAML;

    public function testEveryFactOfAPublishedPluginHasAPublicSourceAndTheCheckDate(): void
    {
        $facts = $this->collector($this->healthy())->plugin(self::SLUG)->facts;

        self::assertSame('v1.10.0', $facts['version']['value'], 'the highest stable version, numerically, not dev-main');
        self::assertSame('^2.2.9 || ^2.3', $facts['sylius_declared']['value']);
        self::assertTrue($facts['sylius_23']['value']);
        self::assertSame('2.2, 2.3', $facts['sylius_tested']['value']);
        self::assertSame('success', $facts['ci']['value']);
        self::assertSame('max', $facts['phpstan']['value']);
        self::assertSame(3, $facts['tests']['value'], 'Test.php and .feature files under tests/, nothing else');
        self::assertArrayNotHasKey('audit', $facts, 'a negative "no scan" is not a figure the plugins page needs: only the demo states its scans');
        foreach ($facts as $key => $fact) {
            self::assertTrue(ProofReader::isPublicSource($fact['source']), $key);
            self::assertSame('2026-10-21', $fact['checked_at'], $key);
        }
    }

    public function testACoverageIsNeverWritten(): void
    {
        $facts = $this->collector($this->healthy())->plugin(self::SLUG)->facts;

        self::assertArrayNotHasKey('coverage', $facts);
    }

    public function testAPluginUnknownToPackagistIsUnpublished(): void
    {
        $sources = $this->healthy(package: null);

        $facts = $this->collector($sources)->plugin(self::SLUG)->facts;

        self::assertSame('unpublished', $facts['version']['state']);
        self::assertSame('https://packagist.org/packages/tmonier/sylius-quick-order-plugin', $facts['version']['source']);
        self::assertArrayNotHasKey('sylius_declared', $facts);
        self::assertArrayNotHasKey('sylius_23', $facts);
        self::assertSame('success', $facts['ci']['value'], 'the CI facts do not depend on Packagist');
    }

    public function testAPackageWithOnlyDevelopmentVersionsIsUnpublished(): void
    {
        $sources = $this->healthy(package: ['versions' => ['dev-main' => ['version' => 'dev-main', 'require' => ['sylius/sylius' => '^2.3']]]]);

        self::assertSame('unpublished', $this->collector($sources)->plugin(self::SLUG)->facts['version']['state']);
    }

    public function testASyliusConstraintThatExcludesTwoPointThreeIsStatedAsSuch(): void
    {
        $sources = $this->healthy(package: ['versions' => ['v1.0.0' => ['version' => 'v1.0.0', 'require' => ['sylius/sylius' => '~2.2.0']]]]);

        self::assertFalse($this->collector($sources)->plugin(self::SLUG)->facts['sylius_23']['value']);
    }

    public function testWhenPackagistFailsTheLastVerifiedValuesAreKeptAndMarkedStale(): void
    {
        $previous = [
            'version' => ['value' => 'v1.0.0', 'source' => 'https://packagist.org/packages/tmonier/sylius-quick-order-plugin', 'checked_at' => '2026-10-14'],
            'sylius_23' => ['value' => true, 'source' => 'https://packagist.org/packages/tmonier/sylius-quick-order-plugin', 'checked_at' => '2026-10-14'],
        ];
        $sources = $this->healthy(package: new SourceUnavailable('Packagist answered 503'));

        $collected = $this->collector($sources)->plugin(self::SLUG, $previous);

        self::assertSame(['packagist'], $collected->failures);
        self::assertSame('v1.0.0', $collected->facts['version']['value']);
        self::assertSame('2026-10-14', $collected->facts['version']['checked_at'], 'the old date, not today');
        self::assertTrue($collected->facts['version']['stale']);
        self::assertSame('success', $collected->facts['ci']['value']);
        self::assertArrayNotHasKey('stale', $collected->facts['ci']);
    }

    public function testWhenGitHubFailsAndNothingWasVerifiedBeforeThereIsNoFactAtAll(): void
    {
        $sources = new FakeSources(
            ['tmonier/sylius-quick-order-plugin' => $this->package()],
            ['repos/' . self::REPO => new SourceUnavailable('rate limit')],
        );

        $collected = $this->collector($sources)->plugin(self::SLUG);

        self::assertSame(['github'], $collected->failures);
        self::assertSame(['version', 'sylius_declared', 'sylius_23'], array_keys($collected->facts));
    }

    public function testTheDeliveryFactsComeFromThePublicWorkflowOfTheDemo(): void
    {
        $workflow = "jobs:\n  quality:\n    steps:\n      - run: vendor/bin/ecs check src tests\n      - run: vendor/bin/phpstan analyse -c phpstan.dist.neon\n"
            . "  audit:\n    steps:\n      - run: composer audit --locked\n  image:\n    steps:\n      - run: docker run trivy image app\n      - name: SBOM (CycloneDX)\n        run: trivy image --format cyclonedx\n";
        $repository = 'thibmonier/sylius-b2b-demo';
        $sources = new FakeSources([], [
            'repos/' . $repository => ['default_branch' => 'main'],
            'repos/' . $repository . '/contents/.github/workflows?ref=main' => [['name' => 'ci.yaml', 'type' => 'file']],
            'repos/' . $repository . '/actions/workflows/ci.yaml/runs?branch=main&status=completed&per_page=1&exclude_pull_requests=true' => ['workflow_runs' => [['conclusion' => 'success', 'html_url' => 'https://github.com/' . $repository . '/actions/runs/1']]],
            'repos/' . $repository . '/git/trees/main?recursive=1' => ['truncated' => false, 'tree' => [
                ['path' => 'tests/Unit/AFooTest.php', 'type' => 'blob'], ['path' => 'tests/Unit/BTest.php', 'type' => 'blob'], ['path' => 'src/Foo.php', 'type' => 'blob'],
            ]],
        ], [
            $repository . ':.github/workflows/ci.yaml' => $workflow,
            $repository . ':phpstan.dist.neon' => "parameters:\n    level: 9\n",
        ]);

        $collected = $this->collector($sources)->delivery($repository);

        self::assertSame([], $collected->failures);
        self::assertSame(2, $collected->facts['tests']['value']);
        self::assertSame('9', $collected->facts['phpstan']['value']);
        self::assertSame('ECS', $collected->facts['ecs']['value']);
        self::assertSame('composer audit', $collected->facts['composer_audit']['value']);
        self::assertSame('Trivy', $collected->facts['image_scan']['value']);
        self::assertSame('CycloneDX', $collected->facts['sbom']['value']);
        self::assertSame('success', $collected->facts['ci']['value']);
        foreach ($collected->facts as $key => $fact) {
            self::assertTrue(ProofReader::isPublicSource($fact['source']), $key);
        }
    }

    /** US-168: the demo counts its own tests locally, so a pull request can carry a proof that matches its own suite. */
    public function testTheDeliveryTestCountIsTheLocalOneWhenGiven(): void
    {
        $repository = 'thibmonier/sylius-b2b-demo';
        $sources = new FakeSources([], [
            'repos/' . $repository => ['default_branch' => 'main'],
            'repos/' . $repository . '/contents/.github/workflows?ref=main' => [],
            'repos/' . $repository . '/git/trees/main?recursive=1' => ['truncated' => false, 'tree' => [['path' => 'tests/Unit/ATest.php', 'type' => 'blob']]],
        ]);

        $collected = $this->collector($sources)->delivery($repository, [], 7);

        self::assertSame(7, $collected->facts['tests']['value']);
        self::assertSame('https://github.com/' . $repository . '/tree/main/tests', $collected->facts['tests']['source']);
    }

    public function testATreeThatGitHubTruncatedIsAFailureNotACount(): void
    {
        $sources = $this->healthy(tree: ['truncated' => true, 'tree' => []]);

        $collected = $this->collector($sources)->plugin(self::SLUG);

        self::assertContains('github', $collected->failures);
        self::assertArrayNotHasKey('tests', $collected->facts);
    }

    private function collector(FakeSources $sources): ProofCollector
    {
        return new ProofCollector($sources, $sources, new MockClock('2026-10-21 10:00:00'));
    }

    /** @return array<string, mixed> */
    private function package(): array
    {
        return ['versions' => [
            'dev-main' => ['version' => 'dev-main', 'require' => ['sylius/sylius' => '^9.9']],
            'v1.9.0' => ['version' => 'v1.9.0', 'require' => ['sylius/sylius' => '^2.2']],
            'v1.10.0' => ['version' => 'v1.10.0', 'require' => ['sylius/sylius' => '^2.2.9 || ^2.3']],
            'v1.11.0-beta1' => ['version' => 'v1.11.0-beta1', 'require' => ['sylius/sylius' => '^2.3']],
        ]];
    }

    /**
     * @param array<string, mixed>|\Throwable|null $package
     * @param array<string, mixed>|null            $tree
     */
    private function healthy(array|\Throwable|null $package = [], ?array $tree = null): FakeSources
    {
        $branch = 'main';

        return new FakeSources(
            ['tmonier/sylius-quick-order-plugin' => [] === $package ? $this->package() : $package],
            [
                'repos/' . self::REPO => ['default_branch' => $branch],
                'repos/' . self::REPO . '/contents/.github/workflows?ref=main' => [['name' => 'build.yaml', 'type' => 'file']],
                'repos/' . self::REPO . '/actions/workflows/build.yaml/runs?branch=main&status=completed&per_page=1&exclude_pull_requests=true' => ['workflow_runs' => [['conclusion' => 'success', 'html_url' => 'https://github.com/' . self::REPO . '/actions/runs/39117178731']]],
                'repos/' . self::REPO . '/git/trees/main?recursive=1' => $tree ?? ['truncated' => false, 'tree' => [
                    ['path' => 'tests/Unit/MoneyTest.php', 'type' => 'blob'],
                    ['path' => 'tests/Functional/CartTest.php', 'type' => 'blob'],
                    ['path' => 'tests/Behat/quick_order.feature', 'type' => 'blob'],
                    ['path' => 'tests/TestApplication/Kernel.php', 'type' => 'blob'],
                    ['path' => 'src/FooTest.php', 'type' => 'blob'],
                ]],
            ],
            [
                self::REPO . ':.github/workflows/build.yaml' => self::WORKFLOW,
                self::REPO . ':phpstan.neon' => "parameters:\n    level: max\n",
            ],
        );
    }
}
