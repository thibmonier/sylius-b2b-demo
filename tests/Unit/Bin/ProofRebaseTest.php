<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * T-TECH-30-02: bin/proof-rebase in throwaway git repositories (a bare "origin" and a clone on a feature branch), with
 * stubs of bin/proof-refresh (writes the number of test files into config/demo/proof.json) and bin/proof-check (fails
 * when PROOF_CHECK_FAILS=1). Nothing is pushed, the real repository is never touched.
 */
final class ProofRebaseTest extends TestCase
{
    private string $root;

    private string $clone;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/proof-rebase-' . bin2hex(random_bytes(4));
        $this->clone = $this->root . '/clone';
        $fs = new Filesystem();
        $fs->mkdir([$this->root . '/seed/bin', $this->root . '/seed/config/demo', $this->root . '/seed/tests']);
        $fs->copy(\dirname(__DIR__, 3) . '/bin/proof-rebase', $this->root . '/seed/bin/proof-rebase');
        file_put_contents($this->root . '/seed/bin/proof-refresh', "#!/bin/sh\ncd \"\$(dirname \"\$0\")/..\"\nprintf '{\"tests\": %s}\\n' \"\$(ls tests | wc -l | tr -d ' ')\" > config/demo/proof.json\n");
        file_put_contents($this->root . '/seed/bin/proof-check', "#!/bin/sh\n[ \"\${PROOF_CHECK_FAILS:-0}\" = 1 ] && { echo 'Proof behind the repository'; exit 1; }\necho 'Proof up to date'\n");
        $fs->chmod([$this->root . '/seed/bin/proof-rebase', $this->root . '/seed/bin/proof-refresh', $this->root . '/seed/bin/proof-check'], 0o755);
        file_put_contents($this->root . '/seed/tests/ATest.php', "a\n");
        file_put_contents($this->root . '/seed/config/demo/proof.json', "{\"tests\": 1}\n");
        file_put_contents($this->root . '/seed/README.md', "readme\n");

        $this->git($this->root, 'init', '-q', '--bare', '-b', 'main', 'origin.git');
        $this->git($this->root . '/seed', 'init', '-q', '-b', 'main');
        $this->commitAll($this->root . '/seed', 'initial');
        $this->git($this->root . '/seed', 'remote', 'add', 'origin', $this->root . '/origin.git');
        $this->git($this->root . '/seed', 'push', '-q', 'origin', 'main');
        $this->git($this->root, 'clone', '-q', $this->root . '/origin.git', 'clone');
        $this->identify($this->clone);
        $this->git($this->clone, 'switch', '-q', '-c', 'feature/x');
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->root);
    }

    public function testItRefusesADirtyTree(): void
    {
        file_put_contents($this->clone . '/README.md', "changed\n");

        $process = $this->rebase();

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('uncommitted changes', $process->getErrorOutput());
    }

    public function testItRefusesTheMainBranch(): void
    {
        $this->git($this->clone, 'switch', '-q', 'main');

        $process = $this->rebase();

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('feature branch', $process->getErrorOutput());
    }

    public function testAConflictOnTheProofOnlyIsResolvedByRegeneratingIt(): void
    {
        $this->addTestOn($this->clone, 'BTest.php', 'feat: b');
        file_put_contents($this->root . '/seed/tests/CTest.php', "c\n");
        $this->addTestOn($this->root . '/seed', 'DTest.php', 'feat: c and d');
        $this->git($this->root . '/seed', 'push', '-q', 'origin', 'main');

        $process = $this->rebase();

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput() . $process->getOutput());
        self::assertSame("{\"tests\": 4}\n", file_get_contents($this->clone . '/config/demo/proof.json'));
        self::assertSame('', trim($this->git($this->clone, 'status', '--porcelain', '--untracked-files=no')));
        self::assertSame('0', trim($this->git($this->clone, 'rev-list', '--count', 'feature/x..origin/main')), 'the branch is on top of main');
        self::assertStringContainsString('Proof up to date', $process->getOutput());
    }

    public function testAConflictOutsideTheProofAbortsAndLeavesTheBranchAsItWas(): void
    {
        file_put_contents($this->clone . '/README.md', "feature\n");
        $this->commitAll($this->clone, 'docs: feature');
        $before = trim($this->git($this->clone, 'rev-parse', 'HEAD'));
        file_put_contents($this->root . '/seed/README.md', "main\n");
        $this->commitAll($this->root . '/seed', 'docs: main');
        $this->git($this->root . '/seed', 'push', '-q', 'origin', 'main');

        $process = $this->rebase();

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString('README.md', $process->getErrorOutput());
        self::assertSame($before, trim($this->git($this->clone, 'rev-parse', 'HEAD')));
        self::assertDirectoryDoesNotExist($this->clone . '/.git/rebase-merge', 'no rebase left in progress');
    }

    public function testAProofThatStillFailsTheCheckFails(): void
    {
        $process = $this->rebase(['PROOF_CHECK_FAILS' => '1']);

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString('Proof behind the repository', $process->getOutput() . $process->getErrorOutput());
    }

    private function addTestOn(string $directory, string $file, string $message): void
    {
        file_put_contents($directory . '/tests/' . $file, "x\n");
        (new Process([$directory . '/bin/proof-refresh']))->mustRun();
        $this->commitAll($directory, $message);
    }

    /**
     * @param array<string, string> $environment
     */
    private function rebase(array $environment = []): Process
    {
        $process = new Process([$this->clone . '/bin/proof-rebase'], $this->clone, $environment + ['GIT_AUTHOR_NAME' => 'T', 'GIT_AUTHOR_EMAIL' => 't@example.test', 'GIT_COMMITTER_NAME' => 'T', 'GIT_COMMITTER_EMAIL' => 't@example.test']);
        $process->run();

        return $process;
    }

    private function commitAll(string $directory, string $message): void
    {
        $this->identify($directory);
        $this->git($directory, 'add', '-A');
        $this->git($directory, 'commit', '-q', '-m', $message);
    }

    private function identify(string $directory): void
    {
        $this->git($directory, 'config', 'user.name', 'T');
        $this->git($directory, 'config', 'user.email', 't@example.test');
        $this->git($directory, 'config', 'commit.gpgsign', 'false');
    }

    private function git(string $directory, string ...$arguments): string
    {
        $process = new Process(['git', ...$arguments], $directory);
        $process->mustRun();

        return $process->getOutput();
    }
}
