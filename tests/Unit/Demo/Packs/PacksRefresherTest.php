<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Packs;

use App\Demo\Packs\PackCollector;
use App\Demo\Packs\PackRepositories;
use App\Demo\Packs\PacksRefresher;
use App\Demo\Proof\Refresh\SourceUnavailable;
use App\Tests\Support\Path;
use App\Tests\Unit\Demo\Proof\Refresh\FakeSources;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

/**
 * @phpstan-import-type PackShape from PackSamples
 *
 * US-178: the whole generation of config/demo/packs.json. A pack whose repository fails keeps its last verified entry,
 * marked stale with its date; the others carry on; the exit status of the command follows the failures.
 */
final class PacksRefresherTest extends TestCase
{
    public function testTheTwoPacksOfTheDecisionAreRefreshed(): void
    {
        self::assertSame(['sylius-pack-ai-headless', 'sylius-pack-compliance-tooling'], PackRepositories::ALL);
    }

    public function testEveryPackIsCollectedAndTheFileIsStampedWithTheDate(): void
    {
        $result = $this->refresher($this->sources())->refresh([]);

        self::assertSame('2026-10-24T10:00:00Z', $result->data()['generated_at']);
        self::assertSame([], $result->failures);
        self::assertSame(['sylius-pack-ai-headless', 'sylius-pack-compliance-tooling'], Path::keys($result->data(), 'packs'));
        self::assertSame('composer require tmonier/sylius-pack-ai-headless', Path::get($result->data(), 'packs.sylius-pack-ai-headless.command'));
    }

    public function testAFailingRepositoryKeepsItsLastVerifiedEntryWhileTheOtherIsRefreshed(): void
    {
        $current = ['packs' => [
            'sylius-pack-ai-headless' => [...PackSamples::pack(), 'checked_at' => '2026-10-14'],
            'sylius-pack-compliance-tooling' => [...PackSamples::otherPack(), 'checked_at' => '2026-10-14'],
        ]];
        $sources = $this->sources(unavailable: ['repos/thibmonier/sylius-pack-compliance-tooling' => new SourceUnavailable('rate limit')]);

        $result = $this->refresher($sources)->refresh($current);

        self::assertSame(['sylius-pack-compliance-tooling' => ['github']], $result->failures);
        self::assertSame('2026-10-24', Path::get($result->data(), 'packs.sylius-pack-ai-headless.checked_at'));
        self::assertNull(Path::get($result->data(), 'packs.sylius-pack-ai-headless.stale'));
        self::assertSame('2026-10-14', Path::get($result->data(), 'packs.sylius-pack-compliance-tooling.checked_at'));
        self::assertTrue(Path::get($result->data(), 'packs.sylius-pack-compliance-tooling.stale'));
    }

    public function testEachSubjectIsReportedToTheCaller(): void
    {
        $reported = [];

        $this->refresher($this->sources())->refresh([], static function (string $subject, array $failed) use (&$reported): void {
            $reported[$subject] = $failed;
        });

        self::assertSame(['sylius-pack-ai-headless' => [], 'sylius-pack-compliance-tooling' => []], $reported);
    }

    /** @param array<string, \Throwable> $unavailable */
    private function sources(array $unavailable = []): FakeSources
    {
        $json = [];
        $files = [];
        foreach (['sylius-pack-ai-headless' => PackSamples::pack(), 'sylius-pack-compliance-tooling' => PackSamples::otherPack()] as $name => $pack) {
            $repository = 'thibmonier/' . $name;
            $json['repos/' . $repository] = ['default_branch' => 'main'];
            $files[$repository . ':composer.json'] = (string) json_encode(['name' => $pack['composer'], 'require' => array_combine(
                array_map(static fn (array $plugin): string => 'tmonier/' . $plugin['slug'], $pack['plugins']),
                array_column($pack['plugins'], 'bound'),
            ), 'suggest' => array_fill_keys(array_map(static fn (array $plugin): string => 'tmonier/' . $plugin['slug'], $pack['suggests']), 'x')]);
            $files[$repository . ':README.md'] = self::readme($pack, 'en');
            $files[$repository . ':README.fr.md'] = self::readme($pack, 'fr');
        }

        return new FakeSources([], $unavailable + $json, $files);
    }

    /** @param PackShape $pack */
    private static function readme(array $pack, string $locale): string
    {
        $rows = '';
        foreach ($pack['plugins'] as $plugin) {
            $rows .= \sprintf("| [%1\$s](https://github.com/thibmonier/%1\$s) | `%2\$s` | %3\$s |\n", $plugin['slug'], $plugin['bound'], $plugin['role'][$locale]);
        }
        $steps = '';
        foreach ($pack['steps'] as $step) {
            $steps .= \sprintf("| %s | `%s` | `%s` | `%s` | %s | %s |\n", substr(substr($step['slug'], 7), 0, -7), $step['bundles'][0], $step['config'][0], $step['routes'][0], $step['migrations'] ? 'yes' : 'no', $step['other'][$locale]);
        }

        return \sprintf("# Sylius Pack: %s\n\n**%s**\n\n```bash\n%s\n```\n\n| Plugin | Bound | Role |\n|---|---|---|\n%s\n| Plugin | B | C | R | M | O |\n|---|---|---|---|---|---|\n%s", $pack['title'][$locale], $pack['summary'][$locale], $pack['command'], $rows, $steps);
    }

    private function refresher(FakeSources $sources): PacksRefresher
    {
        $clock = new MockClock('2026-10-24 10:00:00');

        return new PacksRefresher(new PackCollector($sources, $clock), $clock);
    }
}
