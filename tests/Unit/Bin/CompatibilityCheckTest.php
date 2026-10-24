<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use App\Demo\Proof\Compatibility\Refresh\Fingerprint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * US-171, scenario "matrice en retard": bin/proof-check also checks config/demo/compatibility.json (same mechanism as
 * US-168: no network, nothing written), and bin/compat-refresh refuses a bad call before touching the network.
 */
final class CompatibilityCheckTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/compat-check-' . bin2hex(random_bytes(4));
        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->root . '/tests/Unit/ATest.php', '');
        $filesystem->dumpFile($this->root . '/config/demo/plugins.yaml', "parameters:\n    demo.plugin_catalog:\n        - { slug: sylius-a-plugin }\n        - { slug: sylius-b-plugin }\n");
        $filesystem->dumpFile($this->root . '/config/demo/proof.json', (string) json_encode([
            'generated_at' => '2026-10-23T06:57:31Z',
            'plugins' => ['sylius-a-plugin' => ['version' => ['value' => 'v1.0.0']], 'sylius-b-plugin' => ['version' => ['value' => 'v1.0.0']]],
            'delivery' => ['tests' => ['value' => 1, 'source' => 'https://github.com/thibmonier/sylius-b2b-demo/tree/main/tests', 'checked_at' => '2026-10-23']],
        ]));
        // US-178: the check also reads the packs data; this one is in step with the two pack repositories.
        $filesystem->dumpFile($this->root . '/config/demo/packs.json', (string) json_encode(['generated_at' => '2026-10-24T10:00:00Z', 'packs' => ['sylius-pack-ai-headless' => ['fingerprint' => 'a'], 'sylius-pack-compliance-tooling' => ['fingerprint' => 'b']]]));
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->root);
    }

    public function testACompatibilityFileInStepWithTheCatalogueAndTheClonesPasses(): void
    {
        $this->compatibility(['sylius-a-plugin' => Fingerprint::of('{"a":1}', 'jobs: {}'), 'sylius-b-plugin' => 'fb']);
        $this->clone('sylius-a-plugin', '{"a":1}', 'jobs: {}');
        $before = (string) file_get_contents($this->root . '/config/demo/compatibility.json');

        $process = $this->check('--plugins-dir=' . $this->root . '/clones');

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        self::assertStringContainsString('Compatibility up to date: 2 plugins', $process->getOutput());
        self::assertSame($before, file_get_contents($this->root . '/config/demo/compatibility.json'));
    }

    public function testAPluginMissingFromTheCompatibilityFileFailsWithTheRefreshCommand(): void
    {
        $this->compatibility(['sylius-a-plugin' => 'fa']);

        $process = $this->check();

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString('compatibility plugins: published 1, actual 2', $process->getErrorOutput());
        self::assertStringContainsString('missing from the compatibility data: sylius-b-plugin', $process->getErrorOutput());
        self::assertStringContainsString('php bin/compat-refresh', $process->getErrorOutput());
    }

    public function testAComposerJsonChangedSinceTheLastGenerationIsReported(): void
    {
        $this->compatibility(['sylius-a-plugin' => Fingerprint::of('{"a":1}', 'jobs: {}'), 'sylius-b-plugin' => 'fb']);
        $this->clone('sylius-a-plugin', '{"a":2}', 'jobs: {}');

        $process = $this->check('--plugins-dir=' . $this->root . '/clones');

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString('changed since the last generation: sylius-a-plugin', $process->getErrorOutput());
    }

    public function testTheGapGoesToTheJobSummary(): void
    {
        $this->compatibility(['sylius-a-plugin' => 'fa']);
        $summary = $this->root . '/summary.md';

        $this->check('--summary=' . $summary);

        $text = (string) file_get_contents($summary);
        self::assertStringContainsString('### Published compatibility (compatibility.json)', $text);
        self::assertStringContainsString('missing from the compatibility data: sylius-b-plugin', $text);
    }

    public function testAMissingCompatibilityFileFailsWithAClearMessage(): void
    {
        $process = $this->check();

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('Compatibility unreadable:', $process->getErrorOutput());
        self::assertStringNotContainsString('Stack trace', $process->getErrorOutput() . $process->getOutput());
    }

    public function testTheRefreshRefusesAnUnknownPluginOrOptionBeforeAnyNetworkCall(): void
    {
        foreach (['--only=sylius-unknown-plugin', '--write'] as $option) {
            $process = new Process(['php', \dirname(__DIR__, 3) . '/bin/compat-refresh', '--dry-run', $option]);
            $process->run();

            self::assertSame(2, $process->getExitCode(), $option);
            self::assertStringContainsString('usage:', $process->getErrorOutput());
        }
    }

    /** @param array<string, string> $fingerprints */
    private function compatibility(array $fingerprints): void
    {
        $plugins = [];
        foreach ($fingerprints as $slug => $fingerprint) {
            $plugins[$slug] = ['fingerprint' => $fingerprint];
        }
        new Filesystem()->dumpFile($this->root . '/config/demo/compatibility.json', (string) json_encode(['generated_at' => '2026-10-23T10:00:00Z', 'plugins' => $plugins]));
    }

    private function clone(string $slug, string $composer, string $workflow): void
    {
        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->root . '/clones/' . $slug . '/composer.json', $composer);
        $filesystem->dumpFile($this->root . '/clones/' . $slug . '/.github/workflows/build.yaml', $workflow);
    }

    private function check(string ...$options): Process
    {
        $process = new Process(['php', \dirname(__DIR__, 3) . '/bin/proof-check', '--root=' . $this->root, ...$options]);
        $process->run();

        return $process;
    }
}
