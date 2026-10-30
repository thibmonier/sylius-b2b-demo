<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * T-TECH-37-03: bin/verify-worker-report vérifie qu'un SHA et/ou un run ID existent avant d'accepter un rapport.
 */
final class VerifyWorkerReportTest extends TestCase
{
    public function testValidShaAndRunIdPasses(): void
    {
        // Utiliser le SHA actuel du dépôt
        $sha = trim((string) shell_exec('cd ' . escapeshellarg(\dirname(__DIR__, 3)) . ' && git rev-parse HEAD'));

        $process = new Process([
            \dirname(__DIR__, 3) . '/bin/verify-worker-report',
            $sha,
        ]);
        $process->run();

        self::assertSame(0, $process->getExitCode(), 'Un SHA valide doit passer');
    }

    public function testInvalidShaFails(): void
    {
        $invalidSha = str_repeat('0', 40);

        $process = new Process([
            \dirname(__DIR__, 3) . '/bin/verify-worker-report',
            $invalidSha,
        ]);
        $process->run();

        self::assertSame(1, $process->getExitCode(), 'Un SHA inexistant doit échouer');
        self::assertStringContainsString($invalidSha, $process->getErrorOutput());
    }

    public function testMissingShaIsAUsageError(): void
    {
        $process = new Process([\dirname(__DIR__, 3) . '/bin/verify-worker-report']);
        $process->run();

        self::assertSame(2, $process->getExitCode(), 'Aucun argument doit être une erreur d\'usage');
    }
}
