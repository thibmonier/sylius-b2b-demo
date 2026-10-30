<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * US-208: bin/check-lock-diff compares composer.lock with the one of the base branch, without network. The fixtures
 * are minimal locks (tests/Fixtures/composer-lock): base, ordinary update, rewritten tag, new Composer plugin.
 */
final class CheckLockDiffTest extends TestCase
{
    private string $summary;

    protected function setUp(): void
    {
        $this->summary = sys_get_temp_dir() . '/lock-diff-' . bin2hex(random_bytes(4)) . '.md';
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->summary);
    }

    public function testAnOrdinaryUpdatePassesAndTheSummaryListsPackageOldAndNewVersion(): void
    {
        $process = $this->compare('ordinary-update');

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        $summary = (string) file_get_contents($this->summary);
        self::assertStringContainsString('acme/library', $summary);
        self::assertStringContainsString('1.0.0', $summary);
        self::assertStringContainsString('1.0.1', $summary);
    }

    public function testADevelopmentVersionMayMoveToAnotherCommit(): void
    {
        $process = $this->compare('ordinary-update');

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        self::assertStringContainsString('acme/branch', (string) file_get_contents($this->summary));
    }

    public function testAnUnchangedLockPasses(): void
    {
        $process = $this->compare('base');

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
    }

    public function testARewrittenTagFailsAndNamesPackageVersionAndBothReferences(): void
    {
        $process = $this->compare('rewritten-tag');

        self::assertSame(1, $process->getExitCode());
        $error = $process->getErrorOutput();
        self::assertStringContainsString('acme/library', $error);
        self::assertStringContainsString('1.0.0', $error);
        self::assertStringContainsString(str_repeat('a', 40), $error);
        self::assertStringContainsString(str_repeat('e', 40), $error);
    }

    public function testANewComposerPluginFailsAndIsNamed(): void
    {
        $process = $this->compare('new-plugin');

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString('acme/installer-hook', $process->getErrorOutput());
        self::assertStringContainsString('composer-plugin', $process->getErrorOutput());
    }

    public function testAMissingFileIsAUsageError(): void
    {
        $process = new Process([\dirname(__DIR__, 3) . '/bin/check-lock-diff', '--base-file=/nonexistent/base.json', $this->fixture('base')]);
        $process->run();

        self::assertSame(2, $process->getExitCode());
    }

    // T-TECH-37-06: bin/check-lock-diff lists package additions and removals
    public function testListsAddedPackages(): void
    {
        $baseLock = $this->createLock(['vendor/package-a' => '1.0.0']);
        $headLock = $this->createLock([
            'vendor/package-a' => '1.0.0',
            'vendor/package-b' => '2.0.0',
        ]);

        $output = $this->runCheckLockDiff($baseLock, $headLock);

        self::assertStringContainsString('vendor/package-b', $output);
        self::assertStringContainsString('2.0.0', $output);
        self::assertStringContainsString('—', $output, 'Should show "—" for missing "Before" column');
    }

    public function testListsRemovedPackages(): void
    {
        $baseLock = $this->createLock([
            'vendor/package-a' => '1.0.0',
            'vendor/package-b' => '2.0.0',
        ]);
        $headLock = $this->createLock(['vendor/package-a' => '1.0.0']);

        $output = $this->runCheckLockDiff($baseLock, $headLock);

        self::assertStringContainsString('vendor/package-b', $output);
        self::assertStringContainsString('2.0.0', $output);
        self::assertStringContainsString('—', $output, 'Should show "—" for missing "After" column');
    }

    private function compare(string $head): Process
    {
        $process = new Process([
            \dirname(__DIR__, 3) . '/bin/check-lock-diff',
            '--base-file=' . $this->fixture('base'),
            '--summary=' . $this->summary,
            $this->fixture($head),
        ]);
        $process->run();

        return $process;
    }

    private function fixture(string $name): string
    {
        return \dirname(__DIR__, 2) . '/Fixtures/composer-lock/' . $name . '.json';
    }

    /**
     * @param array<string, string> $packages Map of package name to version
     */
    private function createLock(array $packages): string
    {
        $lockData = ['packages' => [], 'packages-dev' => []];

        foreach ($packages as $name => $version) {
            $lockData['packages'][] = [
                'name' => $name,
                'version' => $version,
                'type' => 'library',
            ];
        }

        return json_encode($lockData, JSON_THROW_ON_ERROR);
    }

    private function runCheckLockDiff(string $baseLockJson, string $headLockJson): string
    {
        $baseLockFile = tempnam(sys_get_temp_dir(), 'base-lock-') . '.json';
        $headLockFile = tempnam(sys_get_temp_dir(), 'head-lock-') . '.json';

        file_put_contents($baseLockFile, $baseLockJson);
        file_put_contents($headLockFile, $headLockJson);

        try {
            $process = new Process([
                'php',
                \dirname(__DIR__, 3) . '/bin/check-lock-diff',
                '--base-file=' . $baseLockFile,
                $headLockFile,
            ]);
            $process->run();

            return $process->getOutput();
        } finally {
            @unlink($baseLockFile);
            @unlink($headLockFile);
        }
    }
}
