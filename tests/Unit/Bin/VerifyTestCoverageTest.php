<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * T-TECH-37-01: bin/verify-test-coverage vérifie que chaque fichier *Test.php de tests/ appartient à une suite
 * déclarée dans phpunit.xml.dist et exécutée en CI.
 */
final class VerifyTestCoverageTest extends TestCase
{
    public function testDetectsOrphanTestFile(): void
    {
        $tempDir = sys_get_temp_dir() . '/test-coverage-' . bin2hex(random_bytes(4));
        mkdir($tempDir . '/tests/Unit', 0777, true);
        mkdir($tempDir . '/tests/Integration', 0777, true);

        $phpunitXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    <testsuites>
        <testsuite name="integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
</phpunit>
XML;
        file_put_contents($tempDir . '/phpunit.xml.dist', $phpunitXml);
        file_put_contents($tempDir . '/tests/Unit/OrphanTest.php', '<?php class OrphanTest {}');

        try {
            $process = new Process([
                \dirname(__DIR__, 3) . '/bin/verify-test-coverage',
                '--phpunit-xml=' . $tempDir . '/phpunit.xml.dist',
                '--tests-dir=' . $tempDir . '/tests',
            ]);
            $process->run();

            self::assertSame(1, $process->getExitCode(), 'Le fichier orphelin doit être détecté');
            self::assertStringContainsString('OrphanTest.php', $process->getErrorOutput());
        } finally {
            $this->rmrf($tempDir);
        }
    }

    public function testResolvesRelativePathsCorrectly(): void
    {
        $tempDir = sys_get_temp_dir() . '/test-coverage-' . bin2hex(random_bytes(4));
        mkdir($tempDir . '/project/tests/Integration', 0777, true);

        $phpunitXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    <testsuites>
        <testsuite name="integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
</phpunit>
XML;
        file_put_contents($tempDir . '/project/phpunit.xml.dist', $phpunitXml);
        file_put_contents($tempDir . '/project/tests/Integration/InstallationTest.php', '<?php class InstallationTest {}');

        try {
            $process = new Process([
                \dirname(__DIR__, 3) . '/bin/verify-test-coverage',
                '--phpunit-xml=' . $tempDir . '/project/phpunit.xml.dist',
                '--tests-dir=' . $tempDir . '/project/tests',
            ], $tempDir);
            $process->run();

            self::assertSame(0, $process->getExitCode(), 'Les chemins relatifs doivent être résolus correctement');
        } finally {
            $this->rmrf($tempDir);
        }
    }

    public function testChecksWorkflowCoverage(): void
    {
        $tempDir = sys_get_temp_dir() . '/test-coverage-' . bin2hex(random_bytes(4));
        mkdir($tempDir . '/tests/Integration', 0777, true);
        mkdir($tempDir . '/tests/Functional', 0777, true);
        mkdir($tempDir . '/.github/workflows', 0777, true);

        $phpunitXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    <testsuites>
        <testsuite name="integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="functional">
            <directory>tests/Functional</directory>
        </testsuite>
    </testsuites>
</phpunit>
XML;
        file_put_contents($tempDir . '/phpunit.xml.dist', $phpunitXml);

        $workflow = <<<'YAML'
name: CI
jobs:
  tests:
    steps:
      - run: vendor/bin/phpunit --testsuite=integration
YAML;
        file_put_contents($tempDir . '/.github/workflows/ci.yaml', $workflow);

        file_put_contents($tempDir . '/tests/Integration/InstallationTest.php', '<?php class InstallationTest {}');
        file_put_contents($tempDir . '/tests/Functional/OrphanTest.php', '<?php class OrphanTest {}');

        try {
            $process = new Process([
                \dirname(__DIR__, 3) . '/bin/verify-test-coverage',
                '--phpunit-xml=' . $tempDir . '/phpunit.xml.dist',
                '--tests-dir=' . $tempDir . '/tests',
                '--workflow=' . $tempDir . '/.github/workflows/ci.yaml',
            ]);
            $process->run();

            self::assertSame(1, $process->getExitCode(), 'Les fichiers hors des suites exécutées en CI doivent être détectés');
            self::assertStringContainsString('OrphanTest.php', $process->getErrorOutput());
        } finally {
            $this->rmrf($tempDir);
        }
    }

    public function testRejectsUnexpectedPositionalArgument(): void
    {
        $process = new Process([
            \dirname(__DIR__, 3) . '/bin/verify-test-coverage',
            'unexpected-arg',
        ]);
        $process->run();

        self::assertSame(2, $process->getExitCode(), 'Un argument positionnel inattendu doit être une erreur d\'usage');
    }

    public function testAllTestFilesAreInASuite(): void
    {
        $process = new Process([
            \dirname(__DIR__, 3) . '/bin/verify-test-coverage',
            '--phpunit-xml=' . \dirname(__DIR__, 3) . '/phpunit.xml.dist',
        ]);
        $process->run();

        self::assertSame(0, $process->getExitCode(), 'Tous les fichiers de tests doivent être dans une suite');
    }

    private function rmrf(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) ?: [] as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->rmrf($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
