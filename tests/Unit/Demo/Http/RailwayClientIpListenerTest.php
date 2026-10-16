<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Http;

use App\Demo\Http\RailwayClientIpListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * US-037 (V1): behind the Railway edge, a visitor-sent X-Forwarded-For survived at the right of the list, so every
 * per-IP limit could be bypassed by changing that header. The edge overwrites X-Real-IP: it becomes the only
 * forwarded address.
 */
final class RailwayClientIpListenerTest extends TestCase
{
    private const EDGE = 'fd12:2712:67b2:1:b000:101:89db:59cc';

    protected function setUp(): void
    {
        Request::setTrustedProxies(['127.0.0.1', self::EDGE], Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO);
    }

    protected function tearDown(): void
    {
        Request::setTrustedProxies([], 0);
    }

    public function testAForgedForwardedForIsReplacedByTheRealIpOfTheEdge(): void
    {
        $request = $this->request(self::EDGE, ['HTTP_X_FORWARDED_FOR' => '88.185.143.226, 203.0.113.9', 'HTTP_X_REAL_IP' => '88.185.143.226']);

        $this->listen($request);

        self::assertSame('88.185.143.226', $request->getClientIp());
    }

    public function testTheSchemeForwardedByTheEdgeIsStillTrusted(): void
    {
        $request = $this->request(self::EDGE, ['HTTP_X_FORWARDED_FOR' => '203.0.113.9', 'HTTP_X_REAL_IP' => '88.185.143.226', 'HTTP_X_FORWARDED_PROTO' => 'https']);

        $this->listen($request);

        self::assertTrue($request->isSecure());
    }

    public function testTheSimulatorLoopbackCallKeepsTheVisitorAddressItForwards(): void
    {
        $request = $this->request('127.0.0.1', ['HTTP_X_FORWARDED_FOR' => '88.185.143.226', 'HTTP_X_REAL_IP' => '10.0.0.1']);

        $this->listen($request);

        self::assertSame('88.185.143.226', $request->getClientIp());
    }

    /** @return iterable<string, array{array<string, string>}> */
    public static function headersWithoutAUsableRealIp(): iterable
    {
        yield 'no X-Real-IP (local run)' => [['HTTP_X_FORWARDED_FOR' => '203.0.113.9']];
        yield 'not an IP' => [['HTTP_X_FORWARDED_FOR' => '203.0.113.9', 'HTTP_X_REAL_IP' => 'evil, 1.2.3.4']];
    }

    /** @param array<string, string> $headers */
    #[\PHPUnit\Framework\Attributes\DataProvider('headersWithoutAUsableRealIp')]
    public function testWithoutAUsableRealIpNoForwardedAddressIsBelieved(array $headers): void
    {
        $request = $this->request(self::EDGE, $headers);

        $this->listen($request);

        self::assertSame(self::EDGE, $request->getClientIp(), 'Fail closed: the edge address (one shared key), never a forged one.');
    }

    /** @param array<string, string> $server */
    private function request(string $remoteAddr, array $server): Request
    {
        return Request::create('https://demo.example.org/fr_FR/', server: ['REMOTE_ADDR' => $remoteAddr] + $server);
    }

    private function listen(Request $request): void
    {
        $kernel = $this->createStub(HttpKernelInterface::class);
        new RailwayClientIpListener()(new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST));
    }
}
