<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use App\Tests\Support\Await;
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
        $fs->copy(\dirname(__DIR__, 3) . '/bin/check-disk', $this->root . '/bin/check-disk');
        $fs->copy(\dirname(__DIR__, 3) . '/bin/check-resources', $this->root . '/bin/check-resources');
        file_put_contents($this->root . '/bin/prepare-test-db', "#!/bin/sh\nexit 0\n");
        // The free space is a stub (50 GB unless a test says otherwise): a run must not depend on the disk of the machine.
        file_put_contents($this->root . '/stub/df', <<<'SH'
            #!/bin/sh
            printf 'Filesystem 1024-blocks Used Available Capacity Mounted on\n/dev/x 999999999 1 %s 50%% /\n' "${STUB_FREE_KB:-52428800}"
            SH);
        $fs->chmod($this->root . '/stub/df', 0o755);
        // Stands for PHPUnit: waits for the release file when asked to, then exits with STUB_PHP_EXIT.
        file_put_contents($this->root . '/stub/php', <<<'SH'
            #!/bin/sh
            while [ -n "${STUB_PHP_WAIT:-}" ] && [ ! -e "$STUB_PHP_WAIT" ]; do sleep 0.05; done
            exit "${STUB_PHP_EXIT:-0}"
            SH);
        // Widens the race window of the stale-lock takeover: every read of a lock PID is delayed by STUB_CAT_DELAY,
        // and logged (one line per read) in STUB_CAT_LOG when set, so a test can wait for a read instead of sleeping.
        file_put_contents($this->root . '/stub/cat', <<<'SH'
            #!/bin/sh
            case "$*" in *run-tests.lock/pid*)
                [ -z "${STUB_CAT_LOG:-}" ] || echo read >> "$STUB_CAT_LOG"
                sleep "${STUB_CAT_DELAY:-0}" ;;
            esac
            exec /bin/cat "$@"
            SH);
        // Same for the removal and the rename of a lock: a slow run acts on the lock after the other one has taken it over.
        foreach (['rm', 'mv'] as $tool) {
            file_put_contents($this->root . '/stub/' . $tool, <<<SH
                #!/bin/sh
                case "\$*" in *run-tests.lock*) sleep "\${STUB_LOCK_OP_DELAY:-0}" ;; esac
                exec /bin/{$tool} "\$@"
                SH);
            $fs->chmod($this->root . '/stub/' . $tool, 0o755);
        }
        $fs->chmod([$this->root . '/bin/run-tests', $this->root . '/bin/check-disk', $this->root . '/bin/prepare-test-db', $this->root . '/stub/php', $this->root . '/stub/cat'], 0o755);
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

    public function testTheTakeoverOfAStaleLockIsLoggedWithTheEvent(): void
    {
        $this->staleLock();

        $result = $this->runToEnd();

        self::assertSame(0, $result->getExitCode(), $result->getErrorOutput());
        self::assertFileExists($this->root . '/var/run-tests.log');
        self::assertStringContainsString('stale lock taken over', (string) file_get_contents($this->root . '/var/run-tests.log'));
    }

    public function testTwoRunsTakingOverTheSameStaleLockLeaveTheLockToExactlyOne(): void
    {
        for ($i = 0; $i < 20; ++$i) {
            $this->staleLock();
            $release = $this->root . '/release-' . $i;
            $env = ['STUB_PHP_WAIT' => $release, 'STUB_CAT_DELAY' => '0.2'];
            $runs = [$this->start($env), $this->start($env + ['STUB_LOCK_OP_DELAY' => '0.6'])];

            $holders = $this->waitForOneToStop($runs);
            touch($release);

            self::assertCount(1, $holders, 'iteration ' . $i . ': exactly one run must keep the lock');
            self::assertSame(0, $holders[0]->wait(), 'iteration ' . $i);
            self::assertDirectoryDoesNotExist($this->root . '/var/run-tests.lock', 'iteration ' . $i);
            $this->removeStaleLeftovers();
        }
    }

    public function testWaitReturnsAtOnceWhenNoRunHoldsTheLock(): void
    {
        $result = $this->runToEnd([], ['--wait']);

        self::assertSame(0, $result->getExitCode(), $result->getErrorOutput());
        self::assertDirectoryDoesNotExist($this->root . '/var/run-tests.lock');
    }

    public function testWaitReturnsAtOnceWhenTheLockIsStale(): void
    {
        $this->staleLock();

        self::assertSame(0, $this->runToEnd([], ['--wait'])->getExitCode());
    }

    public function testWaitBlocksWhileARunHoldsTheLockThenReturnsWhenItEnds(): void
    {
        $release = $this->root . '/release';
        $first = $this->start(['STUB_PHP_WAIT' => $release]);
        $this->waitForLock();
        $reads = $this->root . '/waiter-reads.log';
        $waiter = $this->process(['RUN_TESTS_WAIT_INTERVAL' => '0.1', 'STUB_CAT_LOG' => $reads], ['--wait']);
        $waiter->start();

        // Two reads of the lock by the waiter: it has seen the live run twice and is still polling.
        Await::until(static fn (): bool => is_file($reads) && substr_count((string) file_get_contents($reads), "read\n") >= 2, 10, 'The waiter never read the lock twice.');
        self::assertTrue($waiter->isRunning(), 'the waiter must wait for the lock to disappear');

        touch($release);
        $first->wait();
        self::assertSame(0, $waiter->wait());
    }

    public function testWaitGivesUpAfterTheTimeoutWithAReport(): void
    {
        $first = $this->start(['STUB_PHP_WAIT' => $this->root . '/never']);
        $this->waitForLock();

        $result = $this->runToEnd(['RUN_TESTS_WAIT_TIMEOUT' => '1', 'RUN_TESTS_WAIT_INTERVAL' => '0.2'], ['--wait']);

        self::assertSame(5, $result->getExitCode());
        self::assertStringContainsString('still running', $result->getErrorOutput());
        self::assertStringContainsString('PID ' . trim((string) file_get_contents($this->root . '/var/run-tests.lock/pid')), $result->getErrorOutput());
        $first->signal(\SIGTERM);
        $first->wait();
    }

    public function testTheWorkerIdentityIsRecordedInTheLock(): void
    {
        $first = $this->start(['STUB_PHP_WAIT' => $this->root . '/never', 'WORKER_ID' => 'wf']);
        $this->waitForLock();

        self::assertSame('wf', trim((string) file_get_contents($this->root . '/var/run-tests.lock/worker')));
        $first->signal(\SIGTERM);
        $first->wait();
    }

    public function testTheDiskCheckIsShownButNeverBlocksAboveTheFreeze(): void
    {
        $result = $this->runToEnd(['CHECK_DISK_WARN_GB' => '99999999']);

        self::assertSame(0, $result->getExitCode(), $result->getErrorOutput());
        self::assertStringContainsString('Disk', $result->getErrorOutput() . $result->getOutput());
    }

    /** T-TECH-27-03: a run with no path and no filter is the full suite; it needs 6 GB, a targeted run only the 5 GB of the freeze. */
    public function testTheFullSuiteIsRefusedBelowSixGbBeforeAnythingIsCreated(): void
    {
        $result = $this->runToEnd(['STUB_FREE_KB' => (string) (5 * 1024 * 1024 + 512 * 1024)]);

        self::assertSame(1, $result->getExitCode());
        self::assertStringContainsString('the full suite needs 6 GB', $result->getErrorOutput());
        self::assertDirectoryDoesNotExist($this->root . '/var/run-tests.lock');
    }

    public function testATargetedRunStillStartsBetweenFiveAndSixGb(): void
    {
        $free = ['STUB_FREE_KB' => (string) (5 * 1024 * 1024 + 512 * 1024)];

        self::assertSame(0, $this->runToEnd($free, ['--filter', 'HomeRedesign'])->getExitCode());
        self::assertSame(0, $this->runToEnd($free, ['tests/Unit/Bin'])->getExitCode());
        self::assertSame(0, $this->runToEnd($free, ['--testsuite', 'unit'])->getExitCode());
    }

    public function testTheFullSuiteStartsWithSixGbAndTheThresholdCanBeSimulated(): void
    {
        $six = ['STUB_FREE_KB' => (string) (6 * 1024 * 1024)];

        self::assertSame(0, $this->runToEnd($six)->getExitCode());
        self::assertSame(1, $this->runToEnd($six + ['FULL_SUITE_MIN_GB' => '8'])->getExitCode(), 'a simulated threshold of 8 GB refuses 6 GB');
    }

    public function testTheFreezeBelowFiveGbStillWinsForATargetedRun(): void
    {
        $result = $this->runToEnd(['STUB_FREE_KB' => (string) (4 * 1024 * 1024), 'MIN_FREE_GB' => '5'], ['--filter', 'X']);

        self::assertSame(1, $result->getExitCode());
        self::assertStringContainsString('at least 5 GB required', $result->getErrorOutput());
    }

    public function testARunIsRefusedWhenTheSwapIsAboveTheThreshold(): void
    {
        $result = $this->runToEnd(['MAX_SWAP_MB' => '-1'], ['--filter', 'X']);

        self::assertSame(1, $result->getExitCode());
        self::assertStringContainsString('of swap used, above -1 MB', $result->getErrorOutput());
    }

    private function staleLock(): void
    {
        $dead = new Process(['true']);
        $dead->start();
        $pid = $dead->getPid();
        $dead->wait();
        $this->removeStaleLeftovers();
        mkdir($this->root . '/var/run-tests.lock');
        file_put_contents($this->root . '/var/run-tests.lock/pid', (string) $pid);
    }

    private function removeStaleLeftovers(): void
    {
        new Filesystem()->remove(glob($this->root . '/var/run-tests.lock*') ?: []);
    }

    /**
     * @param list<Process> $runs
     *
     * @return list<Process> the runs still alive once one of them has stopped (the lock holders)
     */
    private function waitForOneToStop(array $runs): array
    {
        $alive = $runs;
        Await::until(static function () use ($runs, &$alive): bool {
            $alive = array_values(array_filter($runs, static fn (Process $run): bool => $run->isRunning()));

            return \count($alive) !== \count($runs);
        }, 10, 'None of the concurrent runs stopped.');

        return $alive;
    }

    /**
     * @param array<string, string> $env
     * @param list<string>          $args
     */
    private function runToEnd(array $env = [], array $args = []): Process
    {
        $process = $this->process($env, $args);
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
     * @param list<string>          $args
     */
    private function process(array $env, array $args = []): Process
    {
        // A hung run fails the test instead of blocking the whole suite (the CI waited hours on a deferred trap).
        return new Process(['sh', $this->root . '/bin/run-tests', ...$args], $this->root, $env + [
            'PATH' => $this->root . '/stub:' . getenv('PATH'),
            'MIN_FREE_GB' => '0',
            'MAX_SWAP_MB' => '1000000',
            'TEST_CACHE_DIR' => $this->root . '/var/cache-lock-test',
        ], null, 30);
    }

    private function waitForLock(): void
    {
        Await::until(fn (): bool => is_file($this->root . '/var/run-tests.lock/pid'), 5, 'The first run never took the lock.');
        self::assertFileExists($this->root . '/var/run-tests.lock/pid');
    }
}
