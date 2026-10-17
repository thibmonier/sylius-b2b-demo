<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Command;

use App\Demo\Command\ConsoleSubprocess;
use App\Demo\Command\ResetStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** US-033: the last failed nightly reset stays visible (to /healthz, hence to an uptime monitor) until a reset succeeds. */
final class ResetStatusTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = sys_get_temp_dir() . '/demo-reset-status-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
    }

    public function testNoFailureIsRecordedAtFirst(): void
    {
        self::assertFalse(new ResetStatus($this->file)->hasFailed());
    }

    public function testAFailureIsRecordedUntilAResetSucceeds(): void
    {
        $status = new ResetStatus($this->file);

        $status->markFailed();
        self::assertTrue(new ResetStatus($this->file)->hasFailed(), 'Another process (the web server) sees it.');

        $status->markSucceeded();
        self::assertFalse(new ResetStatus($this->file)->hasFailed());
    }

    public function testASuccessWithoutPreviousFailureIsHarmless(): void
    {
        $status = new ResetStatus($this->file);

        $status->markSucceeded();

        self::assertFalse($status->hasFailed());
    }

    // -------------------------------------------------------------------------
    // US-049: a reset that dies without reporting (fatal error, kill) must not leave /healthz at 200
    // -------------------------------------------------------------------------

    public function testAResetInProgressIsNotAFailureYet(): void
    {
        $status = new ResetStatus($this->file);

        $status->markStarted();

        self::assertFileExists($this->file);
        self::assertFalse($status->hasFailed(), 'The nightly run must not raise a false alert for the monitor.');
        $status->markSucceeded();
    }

    public function testAResetThatNeverEndedBecomesAFailureOnceTheGraceDelayIsOver(): void
    {
        $status = new ResetStatus($this->file);
        $status->markStarted();

        touch($this->file, time() - ResetStatus::MAX_RUN_SECONDS - 60);

        self::assertTrue($status->hasFailed(), 'A killed process (SIGKILL, out-of-memory kill) leaves nothing but this marker.');
        $status->markSucceeded();
    }

    public function testAStartedResetKeepsThePreviousFailureVisible(): void
    {
        $status = new ResetStatus($this->file);
        $status->markFailed();

        $status->markStarted();

        self::assertTrue($status->hasFailed());
        $status->markSucceeded();
    }

    public function testAFailureAfterTheStartIsRecorded(): void
    {
        $status = new ResetStatus($this->file);
        $status->markStarted();

        $status->markFailed();

        self::assertTrue($status->hasFailed());
        $status->markSucceeded();
    }

    public function testASuccessAfterTheStartRemovesTheMarker(): void
    {
        $status = new ResetStatus($this->file);
        $status->markStarted();

        $status->markSucceeded();

        self::assertFileDoesNotExist($this->file);
        self::assertFalse($status->hasFailed());
    }

    public function testAFatalErrorAfterTheStartLeavesTheFailureMarker(): void
    {
        $process = $this->runInChildProcess('$status->markStarted(); ini_set("memory_limit", "8M"); $data = str_repeat("x", 64 * 1024 * 1024);');

        self::assertNotSame(0, $process->getExitCode(), 'The child must really die on the fatal error.');
        self::assertStringContainsString('Allowed memory size', $process->getOutput() . $process->getErrorOutput());
        self::assertTrue(new ResetStatus($this->file)->hasFailed(), 'The failure is visible at once, without waiting for the grace delay.');
    }

    public function testAnExitBeforeTheEndOfTheResetLeavesTheFailureMarker(): void
    {
        $this->runInChildProcess('$status->markStarted(); exit(3);');

        self::assertTrue(new ResetStatus($this->file)->hasFailed());
    }

    public function testASuccessfulResetLeavesNoMarker(): void
    {
        $process = $this->runInChildProcess('$status->markStarted(); $status->markSucceeded();');

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        self::assertFileDoesNotExist($this->file);
    }

    public function testAnEmptyMarkerReadWhileItIsBeingWrittenIsARunningResetNotAFailure(): void
    {
        file_put_contents($this->file, '');
        $status = new ResetStatus($this->file);

        self::assertFalse($status->hasFailed());
        $status->markSucceeded();
    }

    public function testTheMarkerIsWrittenAtomicallyThroughARenamedTemporaryFile(): void
    {
        $status = new ResetStatus($this->file);

        $status->markStarted();
        $status->markFailed();

        self::assertSame([$this->file], glob($this->file . '*'), 'No temporary file is left next to the marker.');
        self::assertStringStartsWith('failed ', (string) file_get_contents($this->file));
    }

    public function testTheWorstCaseOfAResetStaysBelowTheGraceDelayOfTheMarker(): void
    {
        $fixturesMediaAndOrphansBudget = 600;
        $margin = 300;

        self::assertLessThan(
            ResetStatus::MAX_RUN_SECONDS,
            2 * ConsoleSubprocess::TIMEOUT_SECONDS + $fixturesMediaAndOrphansBudget + $margin,
            'A slow but alive reset must never be reported as dead (false 503 at 03:00).',
        );
    }

    private function runInChildProcess(string $body): Process
    {
        $script = $this->file . '.php';
        file_put_contents($script, \sprintf(
            "<?php\nrequire %s;\n\$status = new App\\Demo\\Command\\ResetStatus(%s);\n%s\n",
            var_export(\dirname(__DIR__, 4) . '/vendor/autoload.php', true),
            var_export($this->file, true),
            $body,
        ));
        $process = new Process([\PHP_BINARY, $script]);
        $process->run();
        @unlink($script);

        return $process;
    }
}
