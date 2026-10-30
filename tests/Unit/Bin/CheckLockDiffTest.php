<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** T-TECH-37-06: bin/check-lock-diff lists package additions and removals. */
final class CheckLockDiffTest extends TestCase
{
    public function testListsAddedPackages(): void
    {
        // Create a base lock with one package
        $baseLock = $this->createLock(['vendor/package-a' => '1.0.0']);
        
        // Create a head lock with two packages (package-a unchanged, package-b added)
        $headLock = $this->createLock([
            'vendor/package-a' => '1.0.0',
            'vendor/package-b' => '2.0.0',
        ]);
        
        $output = $this->runCheckLockDiff($baseLock, $headLock);
        
        // Should list the added package
        self::assertStringContainsString('vendor/package-b', $output);
        self::assertStringContainsString('2.0.0', $output);
        self::assertStringContainsString('—', $output, 'Should show "—" for missing "Before" column');
    }

    public function testListsRemovedPackages(): void
    {
        // Create a base lock with two packages
        $baseLock = $this->createLock([
            'vendor/package-a' => '1.0.0',
            'vendor/package-b' => '2.0.0',
        ]);
        
        // Create a head lock with only package-a (package-b removed)
        $headLock = $this->createLock(['vendor/package-a' => '1.0.0']);
        
        $output = $this->runCheckLockDiff($baseLock, $headLock);
        
        // Should list the removed package
        self::assertStringContainsString('vendor/package-b', $output);
        self::assertStringContainsString('2.0.0', $output);
        self::assertStringContainsString('—', $output, 'Should show "—" for missing "After" column');
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
