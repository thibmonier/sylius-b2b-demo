<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

final class CheckDiskTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/check-disk-' . bin2hex(random_bytes(4));
        $fs = new Filesystem();
        $fs->mkdir([$this->root . '/bin', $this->root . '/stub', $this->root . '/var/cache-a']);
        $fs->copy(\dirname(__DIR__, 3) . '/bin/check-disk', $this->root . '/bin/check-disk');
        file_put_contents($this->root . '/stub/df', <<<'SH'
            #!/bin/sh
            printf 'Filesystem 1024-blocks Used Available Capacity Mounted on\n/dev/x 999999999 1 %s 50%% /\n' "$STUB_FREE_KB"
            SH);
        file_put_contents($this->root . '/stub/du', <<<'SH'
            #!/bin/sh
            printf '%s\tvar/cache-a\n' "${STUB_CACHE_KB:-0}"
            SH);
        file_put_contents($this->root . '/stub/docker', <<<'SH'
            #!/bin/sh
            printf '%s\n' "${STUB_DB_BYTES:-0}"
            SH);
        $fs->chmod([$this->root . '/bin/check-disk', $this->root . '/stub/df', $this->root . '/stub/du', $this->root . '/stub/docker'], 0o755);
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->root);
    }

    public function testItIsSilentAboutAlertsWhenTheDiskHasRoom(): void
    {
        $result = $this->check(['STUB_FREE_KB' => (string) (40 * 1024 * 1024)]);

        self::assertSame(0, $result->getExitCode());
        self::assertStringContainsString('40 GB free', $result->getOutput());
        self::assertStringNotContainsString('ALERT', $result->getOutput());
    }

    public function testItAlertsBelowTheThresholdAndAsksToWarnThibaut(): void
    {
        $result = $this->check(['STUB_FREE_KB' => (string) (12 * 1024 * 1024)]);

        self::assertSame(3, $result->getExitCode());
        self::assertStringContainsString('ALERT', $result->getOutput());
        self::assertStringContainsString('12 GB free', $result->getOutput());
        self::assertStringContainsString('warn Thibaut', $result->getOutput());
    }

    public function testTheShortageIsAttributedToTheMachineWhenTheProjectIsSmall(): void
    {
        $result = $this->check(['STUB_FREE_KB' => (string) (8 * 1024 * 1024), 'STUB_CACHE_KB' => (string) (200 * 1024)]);

        self::assertStringContainsString('Origin: machine', $result->getOutput());
        self::assertStringContainsString('outside the project', $result->getOutput());
    }

    public function testTheShortageIsAttributedToTheProjectWhenItsCachesAndDatabasesCoverIt(): void
    {
        $result = $this->check([
            'STUB_FREE_KB' => (string) (8 * 1024 * 1024),
            'STUB_CACHE_KB' => (string) (4 * 1024 * 1024),
            'STUB_DB_BYTES' => (string) (5 * 1024 * 1024 * 1024),
        ]);

        self::assertStringContainsString('Origin: project', $result->getOutput());
        self::assertStringContainsString('bin/clean-test-caches', $result->getOutput());
    }

    public function testTheThresholdIsConfigurable(): void
    {
        $result = $this->check(['STUB_FREE_KB' => (string) (40 * 1024 * 1024), 'CHECK_DISK_WARN_GB' => '50']);

        self::assertSame(3, $result->getExitCode());
    }

    public function testItReportsTheFreezeBelowTheMinimumWithoutLoweringIt(): void
    {
        $result = $this->check(['STUB_FREE_KB' => (string) (3 * 1024 * 1024)]);

        self::assertSame(1, $result->getExitCode());
        self::assertStringContainsString('bin/run-tests refuses to start below 5 GB', $result->getOutput());
    }

    public function testAnUnreachableMysqlDoesNotBreakTheDiagnosis(): void
    {
        file_put_contents($this->root . '/stub/docker', "#!/bin/sh\nexit 1\n");

        $result = $this->check(['STUB_FREE_KB' => (string) (8 * 1024 * 1024)]);

        self::assertSame(3, $result->getExitCode());
        self::assertStringContainsString('Origin:', $result->getOutput());
    }

    /** @param array<string, string> $env */
    private function check(array $env): Process
    {
        $process = new Process(['sh', $this->root . '/bin/check-disk'], $this->root, [
            'PATH' => $this->root . '/stub:' . getenv('PATH'),
        ] + $env);
        $process->run();

        return $process;
    }
}
