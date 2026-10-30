<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** T-TECH-27-05: the usage and the refusals of bin/render-page (the rendering itself needs the demo database and is checked by hand). */
final class RenderPageTest extends TestCase
{
    public function testHelpExplainsTheUsageAndSucceeds(): void
    {
        $process = $this->render(['--help']);

        self::assertSame(0, $process->getExitCode());
        self::assertStringContainsString('usage: bin/render-page --out=DIR', $process->getOutput());
    }

    /** @return iterable<string, array{list<string>, string}> */
    public static function refusals(): iterable
    {
        yield 'nothing' => [[], 'usage: bin/render-page'];
        yield 'no page' => [['--out=/tmp/render-page-test'], 'usage: bin/render-page'];
        yield 'no directory' => [['/fr_FR/'], 'usage: bin/render-page'];
        yield 'unknown option' => [['--bogus', '--out=/tmp/render-page-test', '/fr_FR/'], 'unknown option --bogus'];
        yield 'unknown channel' => [['--channel=B2X', '--out=/tmp/render-page-test', '/fr_FR/'], 'usage: bin/render-page'];
    }

    /** @param list<string> $arguments */
    #[DataProvider('refusals')]
    public function testItRefusesWhatItCannotRender(array $arguments, string $message): void
    {
        $process = $this->render($arguments);

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString($message, $process->getErrorOutput());
    }

    public function testLoginIsRefusedOutsideTheTestEnvironmentWithTheReason(): void
    {
        $process = $this->render(['--out=/tmp/render-page-test', '--login=claire@acme.example', '/fr_FR/'], ['APP_ENV' => 'prod']);

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('APP_ENV=test only', $process->getErrorOutput());
    }

    public function testAWorkerIdentifierIsRequiredForTheRenderCache(): void
    {
        $process = $this->render(['--out=/tmp/render-page-test', '/fr_FR/'], ['WORKER_ID' => false, 'APP_CACHE_DIR' => false]);

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('WORKER_ID=<worker>', $process->getErrorOutput());
    }

    public function testASecondRenderCacheForTheSameWorkerIsRefused(): void
    {
        $process = $this->render(['--out=/tmp/render-page-test', '/fr_FR/'], ['WORKER_ID' => 'wz', 'APP_CACHE_DIR' => '/tmp/another-render-cache']);

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('one render cache per worker', $process->getErrorOutput());
        self::assertStringContainsString('var/cache-wz-render', $process->getErrorOutput());
    }

    public function testNoPageIsRenderedBelowTheDiskThreshold(): void
    {
        $process = $this->render(['--out=/tmp/render-page-test', '/fr_FR/'], ['WORKER_ID' => 'wz', 'APP_CACHE_DIR' => false, 'CHECK_DISK_WARN_GB' => '100000', 'MIN_FREE_GB' => '100000', 'MYSQL_CONTAINER' => 'no-such-container']);

        self::assertSame(4, $process->getExitCode());
        self::assertStringContainsString('bin/check-disk', $process->getErrorOutput());
        self::assertDirectoryDoesNotExist(\dirname(__DIR__, 3) . '/var/cache-wz-render', 'the kernel is not booted, no cache is created');
    }

    // T-TECH-37-04: bin/render-page respects TEST_DB_NAME and refuses sylius_b2b_demo
    public function testRejectsDevelopmentDatabase(): void
    {
        $process = $this->render(['--out=/tmp/render-page-test', '/fr_FR/'], ['WORKER_ID' => 'test', 'TEST_DB_NAME' => 'sylius_b2b_demo']);

        self::assertSame(1, $process->getExitCode(), 'bin/render-page should refuse TEST_DB_NAME=sylius_b2b_demo');
        self::assertStringContainsString('sylius_b2b_demo', $process->getErrorOutput(), 'Error message should mention the refused database name');
    }

    public function testUsesTestDbNameInDatabaseUrl(): void
    {
        // This test verifies that TEST_DB_NAME is used to construct DATABASE_URL
        // by checking that a non-existent database name causes a connection error
        $process = $this->render(['--out=/tmp/render-page-test', '/fr_FR/'], ['APP_ENV' => 'test', 'WORKER_ID' => 'test', 'TEST_DB_NAME' => 'nonexistent_db_xyz', 'APP_CACHE_DIR' => false]);

        // Should fail trying to connect to nonexistent_db_xyz
        self::assertStringContainsString('nonexistent_db_xyz', $process->getErrorOutput(), 'Should attempt to use TEST_DB_NAME in DATABASE_URL');
    }

    /**
     * @param list<string>                $arguments
     * @param array<string, string|false> $environment
     */
    private function render(array $arguments, array $environment = []): Process
    {
        $process = new Process(['php', \dirname(__DIR__, 3) . '/bin/render-page', ...$arguments], null, $environment);
        $process->run();

        return $process;
    }
}
