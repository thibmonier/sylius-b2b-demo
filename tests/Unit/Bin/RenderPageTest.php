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

    /**
     * @param list<string>          $arguments
     * @param array<string, string> $environment
     */
    private function render(array $arguments, array $environment = []): Process
    {
        $process = new Process(['php', \dirname(__DIR__, 3) . '/bin/render-page', ...$arguments], null, $environment);
        $process->run();

        return $process;
    }
}
