<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Command;

use App\Demo\Command\ConsoleSubprocess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;

/**
 * US-049: cache:clear and cache:warmup of the reset run in a process of their own, with a fresh memory. The project
 * is a temporary directory whose bin/console is a stub that reports how it was called.
 */
final class ConsoleSubprocessTest extends TestCase
{
    private string $projectDir;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir() . '/console-subprocess-' . bin2hex(random_bytes(4));
        mkdir($this->projectDir . '/bin', 0o777, true);
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->projectDir);
    }

    public function testItRunsTheConsoleWithTheSamePhpBinaryTheEnvironmentAndTheCurrentMemoryLimit(): void
    {
        $this->consoleStub('echo json_encode(["pid" => getmypid(), "argv" => array_slice($argv, 1), "memory" => ini_get("memory_limit"), "binary" => PHP_BINARY, "env" => getenv("DEMO_SUBPROCESS_PROBE")]);');
        // A real environment variable, as APP_ENV or DATABASE_URL on the platform: visible to getenv() and $_SERVER.
        putenv('DEMO_SUBPROCESS_PROBE=inherited');
        $_SERVER['DEMO_SUBPROCESS_PROBE'] = 'inherited';
        $output = new BufferedOutput();
        $previousMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '256M');

        try {
            $code = new ConsoleSubprocess($this->projectDir, 'prod')->run('cache:warmup', ['--no-debug'], $output);
        } finally {
            ini_set('memory_limit', (string) $previousMemoryLimit);
            putenv('DEMO_SUBPROCESS_PROBE');
            unset($_SERVER['DEMO_SUBPROCESS_PROBE']);
        }

        /** @var array<string, mixed> $report */
        $report = json_decode($output->fetch(), true, flags: \JSON_THROW_ON_ERROR);
        self::assertSame(0, $code);
        self::assertNotSame(getmypid(), $report['pid'], 'A process of its own, not the one that loaded the fixtures.');
        self::assertSame(['cache:warmup', '--env=prod', '--no-interaction', '--no-debug'], $report['argv']);
        self::assertSame(\PHP_BINARY, $report['binary']);
        self::assertSame('256M', $report['memory'], 'The -d memory_limit=1G of the crontab reaches the sub-process.');
        self::assertSame('inherited', $report['env'], 'APP_ENV, DATABASE_URL and the others are inherited.');
    }

    public function testItReturnsTheExitCodeOfTheSubProcess(): void
    {
        $this->consoleStub('fwrite(STDERR, "boom"); exit(7);');
        $output = new BufferedOutput();

        $code = new ConsoleSubprocess($this->projectDir, 'prod')->run('cache:warmup', [], $output);

        self::assertSame(7, $code);
        self::assertStringContainsString('boom', $output->fetch(), 'Its error output is relayed to the logs.');
    }

    public function testAFatalErrorOfTheSubProcessIsAFailureOfThePhpProcessOnly(): void
    {
        $this->consoleStub('ini_set("memory_limit", "8M"); $data = str_repeat("x", 64 * 1024 * 1024);');

        $code = new ConsoleSubprocess($this->projectDir, 'prod')->run('cache:warmup', [], new BufferedOutput());

        self::assertSame(255, $code);
    }

    private function consoleStub(string $body): void
    {
        file_put_contents($this->projectDir . '/bin/console', "<?php\n" . $body . "\n");
    }
}
