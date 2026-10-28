<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use App\Tests\Support\Await;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * US-207: the `--indexing` option of bin/verify-public-mode (public mode with the showcase pages indexable) against the
 * stub server of the demo: the showcase page is open to robots, a page outside the list and the shop stay noindex,
 * robots.txt announces the sitemap and the sitemap answers. Without the option the script keeps asking for a site
 * closed to robots (VerifyPublicModeTest).
 */
final class VerifyPublicModeIndexingTest extends TestCase
{
    private ?Process $server = null;

    protected function tearDown(): void
    {
        $this->server?->stop();
    }

    public function testTheIndexingChecksPassOnASiteThatIndexesItsShowcase(): void
    {
        $result = $this->verify(['DEMO_STUB_MODE' => 'public', 'DEMO_STUB_INDEXING' => '1']);

        self::assertSame(0, $result->getExitCode(), $result->getOutput() . $result->getErrorOutput());
        self::assertStringNotContainsString('KO ', $result->getOutput());
        self::assertStringContainsString('6  sitemap.xml answers', $result->getOutput());
    }

    public function testTheIndexingChecksFailOnASiteThatStillDisallowsEverything(): void
    {
        $result = $this->verify(['DEMO_STUB_MODE' => 'public']);

        self::assertSame(1, $result->getExitCode());
        self::assertMatchesRegularExpression('/^KO\s+6  robots.txt does not disallow everything\s+expected yes, got no$/m', $result->getOutput());
        self::assertMatchesRegularExpression('/^KO\s+6  showcase page without noindex\s+expected yes, got no$/m', $result->getOutput());
    }

    public function testAPageOutsideTheListWithoutNoindexFailsTheCheck(): void
    {
        $result = $this->verify(['DEMO_STUB_MODE' => 'public', 'DEMO_STUB_INDEXING' => '1', 'DEMO_STUB_BREAK' => '/fr_FR/login']);

        self::assertSame(1, $result->getExitCode());
        self::assertMatchesRegularExpression('/^KO\s+6  page outside the list keeps noindex\s+expected yes, got no$/m', $result->getOutput());
    }

    /** @param array<string, string> $stubEnv */
    private function verify(array $stubEnv): Process
    {
        $probe = stream_socket_server('tcp://127.0.0.1:0');
        self::assertIsResource($probe);
        $name = (string) stream_socket_get_name($probe, false);
        $port = (int) substr($name, (int) strrpos($name, ':') + 1);
        fclose($probe);

        $router = \dirname(__DIR__, 2) . '/Fixtures/bin/demo-mode-router.php';
        $this->server = new Process([\PHP_BINARY, '-S', '127.0.0.1:' . $port, $router], null, $stubEnv + ['PATH' => (string) getenv('PATH')]);
        $this->server->start();
        Await::listening('127.0.0.1', $port, 5);

        $process = new Process([\dirname(__DIR__, 3) . '/bin/verify-public-mode', '--indexing', 'http://127.0.0.1:' . $port], null, ['PATH' => (string) getenv('PATH')]);
        $process->setTimeout(60);
        $process->run();

        return $process;
    }
}
