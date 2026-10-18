<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * US-081: bin/audit-js-dependencies on recorded `yarn audit --json` lines (no network).
 */
final class AuditJsDependenciesTest extends TestCase
{
    private string $input;

    protected function setUp(): void
    {
        $this->input = tempnam(sys_get_temp_dir(), 'audit') ?: '';
    }

    protected function tearDown(): void
    {
        @unlink($this->input);
    }

    public function testTheDocumentedExceptionIsReportedAndDoesNotFail(): void
    {
        $result = $this->audit([$this->advisory('GHSA-vfj7-8cjw-p6xm', 'braces', 'high')], '2026-10-18');

        self::assertSame(0, $result->getExitCode(), $result->getErrorOutput());
        self::assertStringContainsString('EXCEPTION braces GHSA-vfj7-8cjw-p6xm', $result->getOutput());
    }

    public function testAnotherHighAdvisoryFails(): void
    {
        $result = $this->audit([$this->advisory('GHSA-0000-0000-0000', 'other', 'critical')], '2026-10-18');

        self::assertSame(1, $result->getExitCode());
        self::assertStringContainsString('KO other GHSA-0000-0000-0000 (critical)', $result->getErrorOutput());
    }

    public function testALowAdvisoryIsOnlyAWarning(): void
    {
        $result = $this->audit([$this->advisory('GHSA-1111-1111-1111', 'minor', 'low')], '2026-10-18');

        self::assertSame(0, $result->getExitCode());
        self::assertStringContainsString('WARNING   minor', $result->getOutput());
    }

    public function testAnEmptyOutputFailsInsteadOfPassing(): void
    {
        $result = $this->audit([], '2026-10-18', withSummary: false);

        self::assertSame(1, $result->getExitCode());
        self::assertStringContainsString('the audit did not run', $result->getErrorOutput());
    }

    public function testAnExpiredExceptionFailsAgain(): void
    {
        $result = $this->audit([], '2099-01-01');

        self::assertSame(1, $result->getExitCode());
        self::assertStringContainsString('expired', $result->getErrorOutput());
    }

    /** @param list<string> $lines */
    private function audit(array $lines, string $today, bool $withSummary = true): Process
    {
        file_put_contents($this->input, implode("\n", $lines + ($withSummary ? [\count($lines) => '{"type":"auditSummary","data":{"vulnerabilities":{}}}'] : [])));
        $process = new Process([\dirname(__DIR__, 3) . '/bin/audit-js-dependencies', '--input=' . $this->input], null, ['AUDIT_TODAY' => $today]);
        $process->run();

        return $process;
    }

    private function advisory(string $id, string $package, string $severity): string
    {
        return json_encode(['type' => 'auditAdvisory', 'data' => ['advisory' => ['github_advisory_id' => $id, 'module_name' => $package, 'severity' => $severity]]], \JSON_THROW_ON_ERROR);
    }
}
