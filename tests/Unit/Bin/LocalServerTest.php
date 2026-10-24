<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * T-TECH-27-04 / T-TECH-27-05: bin/local-server gives the environment of the shell back to the PHP built-in server (php.ini has
 * variables_order = GPCS, which made the demo answer 503 "not configured safely" on a local prod run), listens on the loopback
 * only, refuses a bad port, and its router serves the files of public/ as they are.
 */
final class LocalServerTest extends TestCase
{
    public function testItGivesTheEnvironmentOfTheShellToTheServer(): void
    {
        self::assertStringContainsString('-d variables_order=EGPCS', $this->script());
    }

    public function testItListensOnTheLoopbackOnlyAndRunsThePreflightFirst(): void
    {
        $script = $this->script();

        self::assertStringContainsString('-S "127.0.0.1:${PORT}"', $script);
        self::assertStringNotContainsString('0.0.0.0', $script);
        self::assertLessThan(strpos($script, 'exec php'), strpos($script, 'bin/console app:demo:preflight'), 'what is missing is listed before the server starts');
    }

    public function testItRefusesAPortThatIsNotANumber(): void
    {
        $process = new Process(['sh', self::root() . '/bin/local-server', 'abc']);
        $process->run();

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('usage: bin/local-server', $process->getErrorOutput());
    }

    public function testTheRouterLeavesAnExistingFileOfThePublicDirectoryToTheServer(): void
    {
        $_SERVER['REQUEST_URI'] = '/favicon.ico';

        self::assertFileExists(self::root() . '/public/favicon.ico');
        self::assertFalse(require self::root() . '/bin/local-router.php');
    }

    private function script(): string
    {
        return (string) file_get_contents(self::root() . '/bin/local-server');
    }

    private static function root(): string
    {
        return \dirname(__DIR__, 3);
    }
}
