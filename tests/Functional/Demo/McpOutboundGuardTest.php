<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Http\OutboundRequestRefused;
use App\Tests\Support\Await;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * US-040 / M5: the HTTP clients of the MCP server are scoped clients built on `http_client.transport`, which the guard
 * of `http_client` does not cover. A request to a host outside the demo is refused before any connection.
 */
final class McpOutboundGuardTest extends KernelTestCase
{
    /** @return iterable<string, array{string}> */
    public static function mcpClients(): iterable
    {
        yield 'api_shop' => ['sylius_mcp_server.http_client.api_shop'];
        yield 'api_shop_merge_patch' => ['sylius_mcp_server.http_client.api_shop_merge_patch'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('mcpClients')]
    public function testARequestToAnExternalHostIsRefused(string $service): void
    {
        $client = $this->client($service);

        $this->expectException(OutboundRequestRefused::class);
        $client->request('GET', 'https://not-the-demo.invalid/api/v2/shop/products');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('mcpClients')]
    public function testARequestToTheShopItselfIsNotRefusedByTheGuard(string $service): void
    {
        $client = $this->client($service);

        try {
            $client->request('GET', 'products', ['timeout' => 0.5])->getStatusCode();
        } catch (OutboundRequestRefused $refused) {
            self::fail('The shop API host is allowed: ' . $refused->getMessage());
        } catch (\Throwable) {
            // Nothing listens on the configured host in the test environment: the guard let the request through.
        }
        $this->addToAssertionCount(1);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('mcpClients')]
    public function testARedirectToAHostOutsideTheDemoIsNotFollowed(string $service): void
    {
        $capture = sys_get_temp_dir() . '/mcp-guard-' . bin2hex(random_bytes(4)) . '.log';
        $port = $this->freePort();
        // 127.1 is the loopback too, but not a host the guard knows: the redirect target is "outside the demo".
        $process = proc_open(
            [\PHP_BINARY, '-S', '127.0.0.1:' . $port, __DIR__ . '/Fixtures/redirect-router.php'],
            [0 => ['file', '/dev/null', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes,
            null,
            ['CAPTURE_FILE' => $capture, 'REDIRECT_TARGET' => 'http://127.1:' . $port . '/hit'] + getenv(),
        );
        self::assertIsResource($process);

        try {
            Await::listening('127.0.0.1', $port, 5);

            $status = $this->client($service)->request('GET', 'http://127.0.0.1:' . $port . '/redirect')->getStatusCode();

            self::assertSame(302, $status, 'The redirect is returned, not followed.');
            self::assertFileDoesNotExist($capture, 'Nothing reached the host outside the demo.');
        } finally {
            proc_terminate($process);
            proc_close($process);
            @unlink($capture);
        }
    }

    private function freePort(): int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        self::assertNotFalse($socket);
        $name = (string) stream_socket_get_name($socket, false);
        fclose($socket);

        return (int) substr($name, (int) strrpos($name, ':') + 1);
    }

    private function client(string $service): HttpClientInterface
    {
        $client = self::getContainer()->get($service);
        self::assertInstanceOf(HttpClientInterface::class, $client);

        return $client;
    }
}
