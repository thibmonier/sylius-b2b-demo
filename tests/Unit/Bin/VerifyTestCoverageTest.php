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
    public function testAllTestFilesAreInASuite(): void
    {
        $process = new Process([
            \dirname(__DIR__, 3) . '/bin/verify-test-coverage',
            '--phpunit-xml=' . \dirname(__DIR__, 3) . '/phpunit.xml.dist',
        ]);
        $process->run();

        self::assertSame(0, $process->getExitCode(), 'Tous les fichiers de tests doivent être dans une suite');
    }

    public function testDetectsOrphanTestFile(): void
    {
        $tempDir = sys_get_temp_dir() . '/test-coverage-' . bin2hex(random_bytes(4));
        mkdir($tempDir . '/tests/Unit', 0777, true);

        // Créer un phpunit.xml.dist qui ne couvre que tests/Integration
        $phpunitXml = <<<XML
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

        // Créer un fichier de test orphelin dans tests/Unit
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

    private function rmrf(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $item) {
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
