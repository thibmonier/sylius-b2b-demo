<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use App\Demo\Packs\PackFingerprint;
use App\Tests\Unit\Demo\Packs\PackSamples;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * US-178, scenario "composition en retard": bin/proof-check also checks config/demo/packs.json (same mechanism as US-168 and
 * US-171: no network, nothing written), and bin/packs-refresh refuses a bad call before touching the network.
 */
final class PacksCheckTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/packs-check-' . bin2hex(random_bytes(4));
        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->root . '/tests/Unit/ATest.php', '');
        $filesystem->dumpFile($this->root . '/config/demo/plugins.yaml', "parameters:\n    demo.plugin_catalog:\n        - { slug: sylius-a-plugin }\n");
        $filesystem->dumpFile($this->root . '/config/demo/proof.json', (string) json_encode([
            'generated_at' => '2026-10-24T06:57:31Z',
            'plugins' => ['sylius-a-plugin' => ['version' => ['value' => 'v1.0.0']]],
            'delivery' => ['tests' => ['value' => 1, 'source' => 'https://github.com/thibmonier/sylius-b2b-demo/tree/main/tests', 'checked_at' => '2026-10-24']],
        ]));
        $filesystem->dumpFile($this->root . '/config/demo/compatibility.json', (string) json_encode(['generated_at' => '2026-10-24T10:00:00Z', 'plugins' => ['sylius-a-plugin' => ['fingerprint' => 'a']]]));
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->root);
    }

    public function testAPacksFileInStepWithTheRepositoriesAndTheClonesPasses(): void
    {
        $this->packs([PackSamples::REPOSITORY => PackFingerprint::of('c', 'e', 'f'), PackSamples::OTHER_REPOSITORY => 'b']);
        $this->clone(PackSamples::REPOSITORY, 'c', 'e', 'f');
        $before = (string) file_get_contents($this->root . '/config/demo/packs.json');

        $process = $this->check('--packs-dir=' . $this->root . '/clones');

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        self::assertStringContainsString('Packs up to date: 2 packs', $process->getOutput());
        self::assertSame($before, file_get_contents($this->root . '/config/demo/packs.json'));
    }

    public function testAPackMissingFromThePacksFileFailsWithTheRefreshCommand(): void
    {
        $this->packs([PackSamples::REPOSITORY => 'a']);

        $process = $this->check();

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString('packs: published 1, expected 2', $process->getErrorOutput());
        self::assertStringContainsString('missing from packs.json: ' . PackSamples::OTHER_REPOSITORY, $process->getErrorOutput());
        self::assertStringContainsString('php bin/packs-refresh', $process->getErrorOutput());
    }

    public function testAReadmeOrComposerJsonChangedSinceTheLastGenerationIsReported(): void
    {
        $this->packs([PackSamples::REPOSITORY => PackFingerprint::of('c', 'e', 'f'), PackSamples::OTHER_REPOSITORY => 'b']);
        $this->clone(PackSamples::REPOSITORY, 'c', 'e2', 'f');

        $process = $this->check('--packs-dir=' . $this->root . '/clones');

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString('changed since the last generation: ' . PackSamples::REPOSITORY, $process->getErrorOutput());
    }

    public function testTheGapGoesToTheJobSummary(): void
    {
        $this->packs([PackSamples::REPOSITORY => 'a']);
        $summary = $this->root . '/summary.md';

        $this->check('--summary=' . $summary);

        $text = (string) file_get_contents($summary);
        self::assertStringContainsString('### Published packs (packs.json)', $text);
        self::assertStringContainsString('missing from packs.json: ' . PackSamples::OTHER_REPOSITORY, $text);
    }

    public function testAMissingPacksFileFailsWithAClearMessage(): void
    {
        $process = $this->check();

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('Packs unreadable:', $process->getErrorOutput());
        self::assertStringNotContainsString('Stack trace', $process->getErrorOutput() . $process->getOutput());
    }

    public function testTheRefreshRefusesAnUnknownOptionBeforeAnyNetworkCall(): void
    {
        foreach (['--only=sylius-pack-unknown', '--write'] as $option) {
            $process = new Process(['php', \dirname(__DIR__, 3) . '/bin/packs-refresh', '--dry-run', $option]);
            $process->run();

            self::assertSame(2, $process->getExitCode(), $option);
            self::assertStringContainsString('usage:', $process->getErrorOutput());
        }
    }

    /** @param array<string, string> $fingerprints */
    private function packs(array $fingerprints): void
    {
        $packs = [];
        foreach ($fingerprints as $repository => $fingerprint) {
            $packs[$repository] = ['fingerprint' => $fingerprint];
        }
        new Filesystem()->dumpFile($this->root . '/config/demo/packs.json', (string) json_encode(['generated_at' => '2026-10-24T10:00:00Z', 'packs' => $packs]));
    }

    private function clone(string $repository, string $composer, string $readme, string $readmeFr): void
    {
        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->root . '/clones/' . $repository . '/composer.json', $composer);
        $filesystem->dumpFile($this->root . '/clones/' . $repository . '/README.md', $readme);
        $filesystem->dumpFile($this->root . '/clones/' . $repository . '/README.fr.md', $readmeFr);
    }

    private function check(string ...$options): Process
    {
        $process = new Process(['php', \dirname(__DIR__, 3) . '/bin/proof-check', '--root=' . $this->root, ...$options]);
        $process->run();

        return $process;
    }
}
