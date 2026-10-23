<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * US-084: bin/check-coverage (line coverage gate of the CI) and the guards of the workflow that calls it.
 */
final class CheckCoverageTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/check-coverage-' . bin2hex(random_bytes(4));
        new Filesystem()->mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->directory);
    }

    public function testItPassesAtOrAboveTheThreshold(): void
    {
        $process = $this->check($this->clover(statements: 100, covered: 90), '90');

        self::assertSame(0, $process->getExitCode());
        self::assertStringContainsString('Line coverage: 90.00 %', $process->getOutput());
    }

    public function testItFailsBelowTheThresholdAndStatesBothFigures(): void
    {
        $process = $this->check($this->clover(statements: 100, covered: 89), '90');

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString('Line coverage: 89.00 %', $process->getOutput());
        self::assertStringContainsString('89.00 % is below the threshold 90.00 %', $process->getErrorOutput());
    }

    public function testAnEmptyReportIsAnError(): void
    {
        self::assertSame(2, $this->check($this->clover(statements: 0, covered: 0), '90')->getExitCode());
    }

    public function testAMissingReportIsAnError(): void
    {
        self::assertSame(2, $this->check($this->directory . '/absent.xml', '90')->getExitCode());
    }

    public function testTheWorkflowRunsRectorAndTheCoverageGate(): void
    {
        $workflow = (string) file_get_contents(\dirname(__DIR__, 3) . '/.github/workflows/ci.yaml');

        // Required checks of the protection of main: their names must not change.
        self::assertStringContainsString('name: Quality and smoke tests', $workflow);
        self::assertStringContainsString('name: Docker image', $workflow);
        self::assertStringContainsString('vendor/bin/rector process --dry-run', $workflow);
        self::assertStringContainsString('php bin/check-coverage build/coverage.xml "${COVERAGE_MIN_LINES}"', $workflow);
        preg_match('/^\s+COVERAGE_MIN_LINES: "(\d+)"$/m', $workflow, $matches);
        self::assertGreaterThanOrEqual(80, (int) ($matches[1] ?? 0));
    }

    private function check(string $clover, string $threshold): Process
    {
        $process = new Process(['php', \dirname(__DIR__, 3) . '/bin/check-coverage', $clover, $threshold]);
        $process->run();

        return $process;
    }

    private function clover(int $statements, int $covered): string
    {
        $path = $this->directory . '/clover.xml';
        file_put_contents($path, \sprintf('<?xml version="1.0"?><coverage><project><metrics statements="%d" coveredstatements="%d"/></project></coverage>', $statements, $covered));

        return $path;
    }
}
