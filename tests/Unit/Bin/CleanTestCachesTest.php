<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * US-141 / T-141-01: bin/clean-test-caches in a throwaway copy of the project with a stub `docker` that records the
 * SQL it receives. Nothing real is removed.
 */
final class CleanTestCachesTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/clean-test-caches-' . bin2hex(random_bytes(4));
        $fs = new Filesystem();
        $fs->mkdir([$this->root . '/bin', $this->root . '/stub', $this->root . '/var/cache-a', $this->root . '/var/cache-wa', $this->root . '/var/cache-wa-x', $this->root . '/var/cache-wa2', $this->root . '/var/cache-wb', $this->root . '/var/cache', $this->root . '/var/log']);
        $fs->copy(\dirname(__DIR__, 3) . '/bin/clean-test-caches', $this->root . '/bin/clean-test-caches');
        $fs->chmod($this->root . '/bin/clean-test-caches', 0o755);
        file_put_contents($this->root . '/stub/docker', <<<'SH'
            #!/bin/sh
            sql=""; for a in "$@"; do sql="$a"; done
            echo "$sql" >> "$STUB_LOG"
            case "$sql" in
                "SHOW DATABASES") printf 'information_schema\nsylius_b2b_demo\nsylius_b2b_demo_test\nsylius_test_old\nb2b_mine\nsylius_b2b_demo_test_x\nother\nb2b_wa\nb2b_wa_x\nb2b_wa2\nb2b_wb\nsylius_test_wa\n' ;;
                "SHOW BINARY LOGS") printf 'binlog.000001\t1024\n' ;;
            esac
            SH);
        $fs->chmod($this->root . '/stub/docker', 0o755);
        // No test run is active unless a test says so (STUB_RUN_TESTS=1).
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->root);
    }

    public function testDryRunRemovesNothingAndNamesTheTargets(): void
    {
        $result = $this->clean(['--dry-run', '--all']);

        self::assertSame(0, $result->getExitCode(), $result->getErrorOutput());
        self::assertDirectoryExists($this->root . '/var/cache-a');
        self::assertStringContainsString('would remove var/cache-a', $result->getOutput());
        self::assertStringContainsString('would drop sylius_test_old', $result->getOutput());
        self::assertStringContainsString('would drop b2b_mine', $result->getOutput());
        self::assertStringContainsString('would drop sylius_b2b_demo_test_x', $result->getOutput());
        self::assertStringContainsString('kept sylius_b2b_demo_test (default test database)', $result->getOutput());
        self::assertStringNotContainsString('DROP', $this->log());
        self::assertStringNotContainsString('PURGE', $this->log());
    }

    public function testRealRunRemovesOnlyTheStaleTargets(): void
    {
        $result = $this->clean(['--all']);

        self::assertSame(0, $result->getExitCode(), $result->getErrorOutput());
        self::assertDirectoryDoesNotExist($this->root . '/var/cache-a');
        self::assertDirectoryExists($this->root . '/var/cache');
        self::assertDirectoryExists($this->root . '/var/log');
        $log = $this->log();
        foreach (['sylius_test_old', 'b2b_mine', 'sylius_b2b_demo_test_x'] as $db) {
            self::assertStringContainsString('DROP DATABASE `' . $db . '`', $log);
        }
        self::assertStringNotContainsString('DROP DATABASE `sylius_b2b_demo`', $log);
        self::assertStringNotContainsString('DROP DATABASE `sylius_b2b_demo_test`', $log);
        self::assertStringNotContainsString('DROP DATABASE `other`', $log);
        self::assertStringContainsString('PURGE BINARY LOGS', $log);
    }

    public function testARealRunIsRefusedWhileTheTestsRunButADryRunIsNot(): void
    {
        $this->lockWithPid((string) getmypid());
        $refused = $this->clean(['--all']);

        self::assertSame(3, $refused->getExitCode());
        self::assertDirectoryExists($this->root . '/var/cache-a');
        self::assertSame('', $this->log());
        self::assertSame(0, $this->clean(['--dry-run', '--all'])->getExitCode());
    }

    public function testAStaleLockDoesNotBlockARealRun(): void
    {
        $dead = new Process(['true']);
        $dead->start();
        $pid = $dead->getPid();
        $dead->wait();
        $this->lockWithPid((string) $pid);

        self::assertSame(0, $this->clean(['--all'])->getExitCode());
        self::assertDirectoryDoesNotExist($this->root . '/var/cache-a');
    }

    public function testAnUnknownOptionIsRefused(): void
    {
        self::assertSame(2, $this->clean(['--force'])->getExitCode());
    }

    public function testAWorkerCleansOnlyItsOwnCachesAndDatabases(): void
    {
        $result = $this->clean([], ['WORKER_ID' => 'wa']);

        self::assertSame(0, $result->getExitCode(), $result->getErrorOutput());
        foreach (['cache-wa', 'cache-wa-x'] as $dir) {
            self::assertDirectoryDoesNotExist($this->root . '/var/' . $dir);
        }
        foreach (['cache-wb', 'cache-wa2', 'cache-a', 'cache'] as $dir) {
            self::assertDirectoryExists($this->root . '/var/' . $dir);
        }
        $log = $this->log();
        foreach (['b2b_wa', 'b2b_wa_x', 'sylius_test_wa'] as $db) {
            self::assertStringContainsString('DROP DATABASE `' . $db . '`', $log);
        }
        foreach (['b2b_wb', 'b2b_wa2', 'b2b_mine', 'sylius_test_old', 'sylius_b2b_demo_test', 'sylius_b2b_demo'] as $db) {
            self::assertStringNotContainsString('DROP DATABASE `' . $db . '`', $log);
        }
    }

    public function testAWorkerDryRunListsOnlyItsOwnTargets(): void
    {
        $result = $this->clean(['--dry-run'], ['WORKER_ID' => 'wb']);

        self::assertStringContainsString('would remove var/cache-wb', $result->getOutput());
        self::assertStringContainsString('would drop b2b_wb', $result->getOutput());
        self::assertStringNotContainsString('cache-wa', $result->getOutput());
        self::assertStringNotContainsString('b2b_wa', $result->getOutput());
    }

    public function testTheGlobalCleanIsRefusedToAWorker(): void
    {
        $result = $this->clean(['--all'], ['WORKER_ID' => 'wa']);

        self::assertSame(5, $result->getExitCode());
        self::assertDirectoryExists($this->root . '/var/cache-wb');
        self::assertSame('', $this->log());
    }

    public function testWithoutAWorkerNorAllNothingIsCleaned(): void
    {
        $result = $this->clean([]);

        self::assertSame(2, $result->getExitCode());
        self::assertDirectoryExists($this->root . '/var/cache-wa');
        self::assertSame('', $this->log());
    }

    public function testAWorkerIdentifierThatCouldMatchOtherWorkersIsRefused(): void
    {
        self::assertSame(2, $this->clean([], ['WORKER_ID' => 'a_b'])->getExitCode());
        self::assertSame(2, $this->clean([], ['WORKER_ID' => 'a*'])->getExitCode());
        self::assertSame('', $this->log());
    }

    public function testAWorkerIsRefusedWhileItsOwnRunHoldsTheLockButNotWhileAnotherWorkersDoes(): void
    {
        $this->lockWithPid((string) getmypid(), 'wa');

        self::assertSame(3, $this->clean([], ['WORKER_ID' => 'wa'])->getExitCode());
        self::assertDirectoryExists($this->root . '/var/cache-wa');
        self::assertSame(0, $this->clean([], ['WORKER_ID' => 'wb'])->getExitCode());
        self::assertDirectoryDoesNotExist($this->root . '/var/cache-wb');
        self::assertDirectoryExists($this->root . '/var/cache-wa');
    }

    private function lockWithPid(string $pid, ?string $worker = null): void
    {
        mkdir($this->root . '/var/run-tests.lock');
        file_put_contents($this->root . '/var/run-tests.lock/pid', $pid);
        if (null !== $worker) {
            file_put_contents($this->root . '/var/run-tests.lock/worker', $worker);
        }
    }

    /**
     * @param list<string>         $args
     * @param array<string,string> $env
     */
    private function clean(array $args, array $env = []): Process
    {
        $process = new Process(['sh', $this->root . '/bin/clean-test-caches', ...$args], $this->root, $env + [
            'WORKER_ID' => false,
            'PATH' => $this->root . '/stub:' . getenv('PATH'),
            'STUB_LOG' => $this->root . '/sql.log',
        ]);
        $process->run();

        return $process;
    }

    private function log(): string
    {
        return is_file($this->root . '/sql.log') ? (string) file_get_contents($this->root . '/sql.log') : '';
    }
}
