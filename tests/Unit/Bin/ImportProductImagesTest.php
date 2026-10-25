<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** US-196: bin/import-product-images refuses a source folder that is empty, the root of the disk or not a folder, and never copies a directory. */
final class ImportProductImagesTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function badSources(): iterable
    {
        yield 'empty' => ['--source-dir='];
        yield 'root of the disk' => ['--source-dir=/'];
        yield 'not a folder' => ['--source-dir=/nonexistent-folder-of-originals'];
        yield 'missing option' => ['--date=2026-10-25'];
    }

    #[DataProvider('badSources')]
    public function testASourceThatIsNotTheFolderOfTheOriginalsIsRefusedWithoutWritingAnything(string $argument): void
    {
        $before = (string) \file_get_contents(self::root() . '/fixtures/images/manifest.csv');
        $process = new Process(['php', self::root() . '/bin/import-product-images', $argument]);
        $process->run();

        self::assertSame(2, $process->getExitCode(), $process->getOutput() . $process->getErrorOutput());
        self::assertStringNotContainsString('photos:', $process->getOutput());
        self::assertSame($before, \file_get_contents(self::root() . '/fixtures/images/manifest.csv'));
    }

    public function testTheImportCodeNeverCopiesMovesOrExecutesAnything(): void
    {
        $files = [self::root() . '/bin/import-product-images', ...(\glob(self::root() . '/src/Demo/Images/Import/*.php') ?: [])];
        foreach ($files as $file) {
            $code = (string) \file_get_contents($file);
            self::assertDoesNotMatchRegularExpression('/\b(copy|rename|exec|shell_exec|system|passthru|proc_open|popen)\s*\(|Filesystem|RecursiveDirectoryIterator|\bcp\s+-/', $code, \basename($file));
        }
    }

    private static function root(): string
    {
        return \dirname(__DIR__, 3);
    }
}
