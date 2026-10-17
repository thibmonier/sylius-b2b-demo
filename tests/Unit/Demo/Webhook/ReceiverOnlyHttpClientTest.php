<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Webhook;

use App\Demo\Http\OutboundRequestRefused;
use App\Demo\Webhook\DemoWebhookTarget;
use App\Demo\Webhook\ReceiverOnlyHttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/** US-029 / T-029-03: emission side, a webhook can only be sent to the demo receiver. */
final class ReceiverOnlyHttpClientTest extends TestCase
{
    public function testTheReceiverIsReachable(): void
    {
        $client = $this->client(true, static fn (): MockResponse => new MockResponse('ok'));

        self::assertSame('ok', $client->request('POST', 'http://127.0.0.1:8080/demo/webhooks/receive')->getContent());
    }

    public function testAnotherHostAllowedByTheGenericGuardIsRefusedWithoutAnyConnection(): void
    {
        $calls = 0;
        $client = $this->client(true, static function () use (&$calls): MockResponse {
            ++$calls;

            return new MockResponse('sent');
        });

        foreach (['http://mailpit:8025/', 'http://127.0.0.1:8080/punchout/cxml', 'http://127.0.0.1:9000/demo/webhooks/receive', 'https://hooks.partner.example/'] as $url) {
            try {
                $client->request('POST', $url);
                self::fail($url . ' must be refused');
            } catch (OutboundRequestRefused) {
                // expected
            }
        }

        self::assertSame(0, $calls);
    }

    public function testTheOptionsKeepTheRestriction(): void
    {
        $client = $this->client(true, static fn (): MockResponse => new MockResponse('ok'))->withOptions(['timeout' => 5]);

        $this->expectException(OutboundRequestRefused::class);
        $client->request('POST', 'https://hooks.partner.example/');
    }

    public function testOutsideTheDemoModeNothingIsRestricted(): void
    {
        $client = $this->client(false, static fn (): MockResponse => new MockResponse('ok'));

        self::assertSame('ok', $client->request('POST', 'https://hooks.partner.example/')->getContent());
    }

    private function client(bool $demoMode, callable $responses): ReceiverOnlyHttpClient
    {
        return new ReceiverOnlyHttpClient(new MockHttpClient($responses), new DemoWebhookTarget(8080), $demoMode);
    }
}
