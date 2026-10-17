<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * US-052 / T-052-02: bin/verify-public-mode against a stub server (`php -S`) that imitates the demo in each mode.
 * Nothing leaves the machine; the real demo is checked by hand with the same script.
 */
final class VerifyPublicModeTest extends TestCase
{
    private ?Process $server = null;

    protected function tearDown(): void
    {
        $this->server?->stop();
    }

    public function testThePublicModeIsAcceptedWhenEveryCheckPasses(): void
    {
        $result = $this->verify(['DEMO_STUB_MODE' => 'public'], 'mug');

        self::assertSame(0, $result->getExitCode(), $result->getOutput() . $result->getErrorOutput());
        self::assertStringNotContainsString('KO ', $result->getOutput());
        self::assertStringContainsString('12 guest product review refused', $result->getOutput());
    }

    public function testAnOpenRegistrationFailsThePublicMode(): void
    {
        $result = $this->verify(['DEMO_STUB_MODE' => 'public', 'DEMO_STUB_BREAK' => '/fr_FR/register']);

        self::assertSame(1, $result->getExitCode());
        self::assertMatchesRegularExpression('/^KO\s+3 {2}registration refused\s+expected 403, got 200$/m', $result->getOutput());
    }

    public function testThePublicChecksFailOnAPrivateDemo(): void
    {
        $result = $this->verify(['DEMO_STUB_MODE' => 'private']);

        self::assertSame(1, $result->getExitCode());
        self::assertMatchesRegularExpression('/^KO\s+1 {2}anonymous visitor, home page\s+expected 200, got 401$/m', $result->getOutput());
        self::assertMatchesRegularExpression('/^KO\s+7 {2}MCP guide without the private note\s+expected no note, got HTTP 401$/m', $result->getOutput());
    }

    public function testThePrivateModeIsAcceptedOnAClosedGate(): void
    {
        $result = $this->verify(['DEMO_STUB_MODE' => 'private'], null, '--private');

        self::assertSame(0, $result->getExitCode(), $result->getOutput() . $result->getErrorOutput());
        self::assertStringContainsString('All checks OK.', $result->getOutput());
    }

    public function testThePrivateModeFailsOnAnOpenDemo(): void
    {
        $result = $this->verify(['DEMO_STUB_MODE' => 'public'], null, '--private');

        self::assertSame(1, $result->getExitCode());
        self::assertMatchesRegularExpression('/^KO\s+anonymous visitor, home page\s+expected 401, got 200$/m', $result->getOutput());
    }

    public function testAMalformedUrlIsRefusedWithoutAnyRequest(): void
    {
        $result = new Process([$this->script(), 'example.com']);
        $result->run();

        self::assertSame(2, $result->getExitCode());
    }

    /** @param array<string, string> $stubEnv */
    private function verify(array $stubEnv, ?string $slug = null, ?string $flag = null): Process
    {
        $port = $this->startStub($stubEnv);
        $command = array_values(array_filter([$this->script(), $flag, 'http://127.0.0.1:' . $port, $slug]));
        $process = new Process($command, null, ['PATH' => (string) getenv('PATH')]);
        $process->setTimeout(60);
        $process->run();

        return $process;
    }

    /** @param array<string, string> $env */
    private function startStub(array $env): int
    {
        $probe = stream_socket_server('tcp://127.0.0.1:0');
        self::assertIsResource($probe);
        $port = (int) substr((string) stream_socket_get_name($probe, false), (int) strrpos((string) stream_socket_get_name($probe, false), ':') + 1);
        fclose($probe);

        $router = \dirname(__DIR__, 2) . '/Fixtures/bin/demo-mode-router.php';
        $this->server = new Process([\PHP_BINARY, '-S', '127.0.0.1:' . $port, $router], null, $env + ['PATH' => (string) getenv('PATH')]);
        $this->server->start();
        for ($i = 0; $i < 50; ++$i) {
            if (false !== ($socket = @fsockopen('127.0.0.1', $port))) {
                fclose($socket);

                return $port;
            }
            usleep(100_000);
        }
        self::fail('The stub server did not start: ' . $this->server->getErrorOutput());
    }

    private function script(): string
    {
        return \dirname(__DIR__, 3) . '/bin/verify-public-mode';
    }
}
