<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Http;

use App\Demo\Http\OutboundGuardHttpClient;
use App\Demo\Http\OutboundRequestRefused;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/** US-036 / T-036-06: no outbound call from the public demo reaches a host outside it. */
final class OutboundGuardHttpClientTest extends TestCase
{
    public function testARequestToAnExternalHostFailsWithoutAnyConnection(): void
    {
        $calls = 0;
        $client = $this->client(static function () use (&$calls): MockResponse {
            ++$calls;

            return new MockResponse('sent');
        });

        $this->expectException(OutboundRequestRefused::class);

        try {
            $client->request('POST', 'https://hooks.partner.example/webhook', ['body' => 'payload']);
        } finally {
            self::assertSame(0, $calls, 'The inner client was never called.');
        }
    }

    public function testInternalHostsAreReachable(): void
    {
        $client = $this->client(static fn (): MockResponse => new MockResponse('ok'));

        foreach (['http://mailpit:8025/api/v1/messages', 'http://127.0.0.1:8080/punchout/cxml', 'https://api.iconify.design/mdi.json', 'http://mailpit.railway.internal:8025/'] as $url) {
            self::assertSame('ok', $client->request('GET', $url)->getContent(), $url);
        }
    }

    public function testARelativeUrlIsJudgedByItsBaseUri(): void
    {
        $client = $this->client(static fn (): MockResponse => new MockResponse('ok'));

        self::assertSame('ok', $client->request('GET', '/status', ['base_uri' => 'http://mailpit:8025'])->getContent());
        $this->expectException(OutboundRequestRefused::class);
        $client->request('GET', '/status', ['base_uri' => 'https://api.partner.example']);
    }

    public function testTheGuardSurvivesWithOptions(): void
    {
        $client = $this->client(static fn (): MockResponse => new MockResponse('ok'))->withOptions(['timeout' => 2]);

        $this->expectException(OutboundRequestRefused::class);
        $client->request('GET', 'https://example.com/');
    }

    public function testNothingIsBlockedOutsideTheDemoMode(): void
    {
        $client = new OutboundGuardHttpClient(new MockHttpClient(new MockResponse('ok')), false, '');

        self::assertSame('ok', $client->request('GET', 'https://example.com/')->getContent());
    }

    public function testPsr18CallersSeeANetworkErrorTheyAlreadyHandle(): void
    {
        $psr = new \Symfony\Component\HttpClient\Psr18Client($this->client(static fn (): MockResponse => new MockResponse('x')));

        $this->expectException(\Psr\Http\Client\ClientExceptionInterface::class);
        $psr->sendRequest($psr->createRequest('GET', 'https://hub.sylius.com/check-for-updates'));
    }

    private function client(callable $responses): OutboundGuardHttpClient
    {
        return new OutboundGuardHttpClient(new MockHttpClient($responses), true, 'mailpit,api.iconify.design');
    }
}
