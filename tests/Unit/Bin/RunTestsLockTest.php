<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

final class RunTestsLockTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/run-tests-lock-' . bin2hex(random_bytes(4));
        $fs = new Filesystem();
        $fs->mkdir([$this->root . '/bin', $this->root . '/stub', $this->root . '/var']);
        $fs->copy(\dirname(__DIR__, 3) . '/bin/run-tests', $this->root . '/bin/run-tests');
        file_put_contents($this->root . '/bin/prepare-test-db', "#!/bin/sh\nexit 0\n");
        // Stands for PHPUnit: waits for the release file when asked to, then exits with STUB_PHP_EXIT.
        file_put_contents($this->root . '/stub/php', <<<'SH'
            #!/bin/sh
            while [ -n "${STUB_PHP_WAIT:-}" ] && [ ! -e "$STUB_PHP_WAIT" ]; do sleep 0.05; done
            exit "${STUB_PHP_EXIT:-0}"
            SH);
        $fs->chmod([$this->root . '/bin/run-tests', $this->root . '/bin/prepare-test-db', $this->root . '/stub/php'], 0o755);
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->root);
    }

    public function testASecondRunIsRefusedAtOnceWhileTheFirstOneLives(): void
    {
        $release = $this->root . '/release';
        $first = $this->start(['STUB_PHP_WAIT' => $release]);
        $this->waitForLock();

        $second = $this->runToEnd();

        self::assertSame(4, $second->getExitCode());
        self::assertStringContainsString('already running', $second->getErrorOutput());
        self::assertStringContainsString('PID ' . trim((string) file_get_contents($this->root . '/var/run-tests.lock/pid')), $second->getErrorOutput());

        touch($release);
        self::assertSame(0, $first->wait());
    }

    public function testTheLockIsRemovedAtTheEndEvenWhenTheTestsFail(): void
    {
        $result = $this->runToEnd(['STUB_PHP_EXIT' => '3']);

        self::assertSame(3, $result->getExitCode());
        self::assertDirectoryDoesNotExist($this->root . '/var/run-tests.lock');
    }

    public function testTheLockIsRemovedWhenTheRunIsTerminated(): void
    {
        $first = $this->start(['STUB_PHP_WAIT' => $this->root . '/never']);
        $this->waitForLock();

        $first->signal(\SIGTERM);
        $first->wait();

        self::assertDirectoryDoesNotExist($this->root . '/var/run-tests.lock');
    }

    public function testALockLeftByADeadProcessIsTakenOver(): void
    {
        $dead = new Process(['true']);
        $dead->start();
        $pid = $dead->getPid();
        $dead->wait();
        mkdir($this->root . '/var/run-tests.lock');
        file_put_contents($this->root . '/var/run-tests.lock/pid', (string) $pid);

        $result = $this->runToEnd();

        self::assertSame(0, $result->getExitCode(), $result->getErrorOutput());
        self::assertStringContainsString('Stale lock', $result->getErrorOutput());
        self::assertDirectoryDoesNotExist($this->root . '/var/run-tests.lock');
    }

    /**
     * @param array<string, string> $env
     */
    private function runToEnd(array $env = []): Process
    {
        $process = $this->process($env);
        $process->run();

        return $process;
    }

    /**
     * @param array<string, string> $env
     */
    private function start(array $env): Process
    {
        $process = $this->process($env);
        $process->start();

        return $process;
    }

    /**
     * @param array<string, string> $env
     */
    private function process(array $env): Process
    {
        return new Process(['sh', $this->root . '/bin/run-tests'], $this->root, [
            'PATH' => $this->root . '/stub:' . getenv('PATH'),
            'MIN_FREE_GB' => '0',
            'TEST_CACHE_DIR' => $this->root . '/var/cache-lock-test',
        ] + $env);
    }

    private function waitForLock(): void
    {
        for ($i = 0; $i < 100 && !is_file($this->root . '/var/run-tests.lock/pid'); ++$i) {
            usleep(50_000);
        }
        self::assertFileExists($this->root . '/var/run-tests.lock/pid');
    }
}
