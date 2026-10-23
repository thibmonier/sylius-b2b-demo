<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof\Refresh;

use App\Demo\Proof\Check\ProofFreshness;
use App\Demo\Proof\Refresh\ProofCollector;
use App\Demo\Proof\Refresh\ProofRefresher;
use App\Demo\Proof\Refresh\SourceUnavailable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

/** US-111: a refresh touches only the plugins it is asked for, keeps the others, and reports the sources that failed. */
final class ProofRefresherTest extends TestCase
{
    public function testOnlyTheRequestedPluginsAreRefreshedAndTheOthersAreKept(): void
    {
        $current = ['plugins' => ['sylius-other-plugin' => ['version' => ['value' => 'v1.0.0', 'source' => 'https://packagist.org/packages/tmonier/sylius-other-plugin', 'checked_at' => '2026-10-14']]]];
        $sources = new FakeSources(
            ['tmonier/sylius-a-plugin' => null],
            ['repos/thibmonier/sylius-a-plugin' => new SourceUnavailable('down')],
        );

        $result = $this->refresher($sources)->refresh($current, ['sylius-a-plugin'], null);

        self::assertSame('v1.0.0', $result->plugins['sylius-other-plugin']['version']['value']);
        self::assertSame('unpublished', $result->plugins['sylius-a-plugin']['version']['state']);
        self::assertSame(['sylius-a-plugin' => ['github']], $result->failures);
        self::assertSame('2026-10-21T10:00:00Z', $result->generatedAt);
        self::assertSame(['sylius-a-plugin', 'sylius-other-plugin'], array_keys($result->plugins), 'sorted by slug');
    }

    public function testTheDeliveryIsKeptWhenItIsNotRequested(): void
    {
        $current = ['delivery' => ['sbom' => ['value' => 'CycloneDX', 'source' => 'https://github.com/thibmonier/sylius-b2b-demo/blob/main/.github/workflows/ci.yaml', 'checked_at' => '2026-10-14']]];

        $result = $this->refresher(new FakeSources())->refresh($current, [], null);

        self::assertSame($current['delivery'], $result->delivery);
    }

    /** US-168, « rafraîchissement après écart »: a refresh with the local count makes the check pass. */
    public function testARefreshWithTheLocalTestCountMakesTheCheckPass(): void
    {
        $repository = 'thibmonier/sylius-b2b-demo';
        $current = [
            'plugins' => ['sylius-a-plugin' => ['version' => ['value' => 'v1.0.0', 'source' => 'https://packagist.org/packages/tmonier/sylius-a-plugin', 'checked_at' => '2026-10-14']]],
            'delivery' => ['tests' => ['value' => 188, 'source' => 'https://github.com/' . $repository . '/tree/main/tests', 'checked_at' => '2026-10-14']],
        ];
        self::assertNotSame([], ProofFreshness::gaps($current, 190, ['sylius-a-plugin']));
        $sources = new FakeSources([], [
            'repos/' . $repository => ['default_branch' => 'main'],
            'repos/' . $repository . '/contents/.github/workflows?ref=main' => [],
            'repos/' . $repository . '/git/trees/main?recursive=1' => ['truncated' => false, 'tree' => []],
        ]);

        $result = $this->refresher($sources)->refresh($current, [], $repository, null, 190);

        self::assertSame([], ProofFreshness::gaps($result->data(), 190, ['sylius-a-plugin']));
    }

    private function refresher(FakeSources $sources): ProofRefresher
    {
        $clock = new MockClock('2026-10-21 10:00:00');

        return new ProofRefresher(new ProofCollector($sources, $sources, $clock), $clock);
    }
}
