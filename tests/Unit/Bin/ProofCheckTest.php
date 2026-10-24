<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * US-168: bin/proof-check, the check that proof.json is not behind the repository (no network, no write), and the CI
 * step that runs it.
 */
final class ProofCheckTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/proof-check-' . bin2hex(random_bytes(4));
        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->root . '/tests/Unit/ATest.php', '');
        $filesystem->dumpFile($this->root . '/tests/Behat/b.feature', '');
        $filesystem->dumpFile($this->root . '/config/demo/plugins.yaml', "parameters:\n    demo.plugin_catalog:\n        - { slug: sylius-a-plugin }\n        - { slug: sylius-b-plugin }\n");
        // US-171: the check also reads the compatibility data; this one is in step with the catalogue above.
        $filesystem->dumpFile($this->root . '/config/demo/compatibility.json', (string) json_encode(['generated_at' => '2026-10-23T10:00:00Z', 'plugins' => ['sylius-a-plugin' => ['fingerprint' => 'a'], 'sylius-b-plugin' => ['fingerprint' => 'b']]]));
        // US-178: and the packs data; this one is in step with the two pack repositories.
        $filesystem->dumpFile($this->root . '/config/demo/packs.json', (string) json_encode(['generated_at' => '2026-10-24T10:00:00Z', 'packs' => ['sylius-pack-ai-headless' => ['fingerprint' => 'a'], 'sylius-pack-compliance-tooling' => ['fingerprint' => 'b']]]));
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->root);
    }

    public function testAnUpToDateProofPassesWithoutWritingAnything(): void
    {
        $proof = $this->proof(tests: 2, slugs: ['sylius-a-plugin', 'sylius-b-plugin']);
        $before = (string) file_get_contents($proof);

        $process = $this->check();

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        self::assertStringContainsString('Proof up to date: 2 test files, 2 plugins', $process->getOutput());
        self::assertSame($before, file_get_contents($proof));
    }

    public function testAProofBehindTheSuiteFailsWithEachFigure(): void
    {
        $this->proof(tests: 1, slugs: ['sylius-a-plugin']);

        $process = $this->check();

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString('test files: published 1, actual 2 (difference +1)', $process->getErrorOutput());
        self::assertStringContainsString('plugins: published 1, actual 2 (difference +1)', $process->getErrorOutput());
        self::assertStringContainsString('missing from the proof: sylius-b-plugin', $process->getErrorOutput());
        self::assertStringContainsString('php bin/proof-refresh', $process->getErrorOutput());
    }

    public function testTheGapGoesToTheJobSummaryWithTheRefreshCommand(): void
    {
        $this->proof(tests: 1, slugs: ['sylius-a-plugin', 'sylius-b-plugin']);
        $summary = $this->root . '/summary.md';

        $this->check('--summary=' . $summary);

        $text = (string) file_get_contents($summary);
        self::assertStringContainsString('### Published proof (proof.json)', $text);
        self::assertStringContainsString('test files: published 1, actual 2 (difference +1)', $text);
        self::assertStringContainsString('php bin/proof-refresh', $text);
    }

    /** @return iterable<string, array{string|null}> */
    public static function unreadableProofs(): iterable
    {
        yield 'missing' => [null];
        yield 'malformed' => ['{"plugins": '];
    }

    #[DataProvider('unreadableProofs')]
    public function testAnUnreadableProofFailsWithAClearMessageAndNoStackTrace(?string $content): void
    {
        if (null !== $content) {
            new Filesystem()->dumpFile($this->root . '/config/demo/proof.json', $content);
        }

        $process = $this->check();

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('Proof unreadable:', $process->getErrorOutput());
        self::assertStringNotContainsString('Stack trace', $process->getErrorOutput() . $process->getOutput());
        self::assertStringNotContainsString('#0 ', $process->getErrorOutput() . $process->getOutput());
    }

    public function testAnUnknownOptionIsAUsageError(): void
    {
        self::assertSame(2, $this->check('--write')->getExitCode());
    }

    public function testTheWorkflowRunsTheCheckInformativeOnPullRequestsAndBlockingOnMain(): void
    {
        $workflow = (string) file_get_contents(\dirname(__DIR__, 3) . '/.github/workflows/ci.yaml');

        self::assertStringContainsString('php bin/proof-check --summary="$GITHUB_STEP_SUMMARY"', $workflow);
        self::assertStringContainsString("continue-on-error: \${{ github.event_name == 'pull_request' }}", $workflow);
    }

    /** @param list<string> $slugs */
    private function proof(int $tests, array $slugs): string
    {
        $path = $this->root . '/config/demo/proof.json';
        new Filesystem()->dumpFile($path, (string) json_encode([
            'generated_at' => '2026-10-23T06:57:31Z',
            'plugins' => array_fill_keys($slugs, ['version' => ['value' => 'v1.0.0']]),
            'delivery' => ['tests' => ['value' => $tests, 'source' => 'https://github.com/thibmonier/sylius-b2b-demo/tree/main/tests', 'checked_at' => '2026-10-23']],
        ]));

        return $path;
    }

    private function check(string ...$options): Process
    {
        $process = new Process(['php', \dirname(__DIR__, 3) . '/bin/proof-check', '--root=' . $this->root, ...$options]);
        $process->run();

        return $process;
    }
}
