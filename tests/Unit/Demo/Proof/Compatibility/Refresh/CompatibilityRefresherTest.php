<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof\Compatibility\Refresh;

use App\Demo\Proof\Compatibility\Refresh\CompatibilityCollector;
use App\Demo\Proof\Compatibility\Refresh\CompatibilityRefresher;
use App\Demo\Proof\ProofReader;
use App\Demo\Proof\Refresh\SourceUnavailable;
use App\Tests\Support\Path;
use App\Tests\Unit\Demo\Proof\Refresh\FakeSources;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

/** US-171: the whole generation: plugins, the demo's own stack, and the failures that keep the last verified value. */
final class CompatibilityRefresherTest extends TestCase
{
    private const LOCK_SOURCE = 'https://github.com/thibmonier/sylius-b2b-demo/blob/main/composer.lock';

    public function testEachPluginCarriesTheVersionInstalledInTheDemoAndTheDemoStackIsPublished(): void
    {
        $result = $this->refresher($this->sources())->refresh([], ['sylius-a-plugin', 'sylius-b-plugin'], $this->lock());

        $data = $result->data();
        self::assertSame([], $result->failures);
        self::assertSame(['sylius-a-plugin', 'sylius-b-plugin'], Path::keys($data, 'plugins'));
        self::assertSame('v1.2.0', Path::get($data, 'plugins.sylius-a-plugin.demo.version'));
        self::assertSame(self::LOCK_SOURCE, Path::get($data, 'plugins.sylius-a-plugin.demo.source'));
        self::assertNull(Path::get($data, 'plugins.sylius-b-plugin.demo'), 'not installed in the demo: nothing is claimed');
        self::assertSame('2.3.2', Path::get($data, 'demo.sylius'));
        self::assertSame('8.1.9', Path::get($data, 'demo.symfony'));
        self::assertTrue(ProofReader::isPublicSource(Path::get($data, 'demo.source')));
        self::assertSame('2026-10-23T10:00:00Z', Path::get($data, 'generated_at'));
    }

    public function testAFailingRepositoryDoesNotStopTheOthersAndKeepsItsLastValue(): void
    {
        $current = ['plugins' => ['sylius-b-plugin' => ['ci' => ['php' => ['8.3'], 'sylius' => ['2.3'], 'symfony' => ['7.4'], 'database' => ['mysql:8.4'], 'source' => 'https://github.com/thibmonier/sylius-b-plugin/blob/main/.github/workflows/build.yaml', 'checked_at' => '2026-10-14'], 'fingerprint' => 'old']]];
        $sources = $this->sources(broken: 'sylius-b-plugin');
        $notes = [];

        $result = $this->refresher($sources)->refresh($current, ['sylius-a-plugin', 'sylius-b-plugin'], $this->lock(), static function (string $subject, array $failed) use (&$notes): void {
            $notes[$subject] = $failed;
        });

        $data = $result->data();
        self::assertSame(['sylius-b-plugin' => ['github']], $result->failures);
        self::assertSame(['sylius-a-plugin' => [], 'sylius-b-plugin' => ['github'], 'demo' => []], $notes);
        self::assertSame(['8.3', '8.4'], Path::get($data, 'plugins.sylius-a-plugin.ci.php'));
        self::assertTrue(Path::get($data, 'plugins.sylius-b-plugin.ci.stale'));
        self::assertSame('2026-10-14', Path::get($data, 'plugins.sylius-b-plugin.ci.checked_at'));
    }

    public function testAnUnreadableLockKeepsTheLastDemoStack(): void
    {
        $current = ['demo' => ['sylius' => '2.3.1', 'symfony' => '8.1.8', 'php' => null, 'source' => self::LOCK_SOURCE, 'checked_at' => '2026-10-14']];

        $result = $this->refresher($this->sources())->refresh($current, ['sylius-a-plugin'], '{ not json');

        $data = $result->data();
        self::assertSame(['demo' => ['composer.lock']], $result->failures);
        self::assertSame('2.3.1', Path::get($data, 'demo.sylius'));
        self::assertTrue(Path::get($data, 'demo.stale'));
        self::assertSame('2026-10-14', Path::get($data, 'demo.checked_at'));
    }

    public function testThePluginsNotRefreshedKeepTheirFacts(): void
    {
        $current = ['plugins' => ['sylius-a-plugin' => ['fingerprint' => 'kept'], 'sylius-z-plugin' => ['fingerprint' => 'zzz']]];

        $data = $this->refresher($this->sources())->refresh($current, ['sylius-a-plugin'], $this->lock())->data();

        self::assertSame('zzz', Path::get($data, 'plugins.sylius-z-plugin.fingerprint'));
        self::assertNotSame('kept', Path::get($data, 'plugins.sylius-a-plugin.fingerprint'));
    }

    private function refresher(FakeSources $sources): CompatibilityRefresher
    {
        $clock = new MockClock('2026-10-23 10:00:00');

        return new CompatibilityRefresher(new CompatibilityCollector($sources, $clock), $clock);
    }

    private function lock(): string
    {
        return (string) json_encode(['packages' => [
            ['name' => 'sylius/sylius', 'version' => 'v2.3.2'],
            ['name' => 'symfony/framework-bundle', 'version' => 'v8.1.9'],
            ['name' => 'tmonier/sylius-a-plugin', 'version' => 'v1.2.0'],
        ]]);
    }

    private function sources(?string $broken = null): FakeSources
    {
        $json = [];
        $files = [];
        foreach (['sylius-a-plugin', 'sylius-b-plugin'] as $slug) {
            $repository = 'thibmonier/' . $slug;
            $json['repos/' . $repository] = $slug === $broken ? new SourceUnavailable('rate limit') : ['default_branch' => 'main'];
            $json['repos/' . $repository . '/contents/.github/workflows?ref=main'] = [['name' => 'build.yaml', 'type' => 'file']];
            $files[$repository . ':composer.json'] = '{"require":{"php":"^8.2"}}';
            $files[$repository . ':.github/workflows/build.yaml'] = "jobs:\n    t:\n        strategy:\n            matrix:\n                php: [\"8.3\", \"8.4\"]\n                sylius: [\"~2.3.0\"]\n                symfony: [\"^7.4\"]\n                database: [\"mysql:8.4\"]\n";
        }

        return new FakeSources([], $json, $files);
    }
}
