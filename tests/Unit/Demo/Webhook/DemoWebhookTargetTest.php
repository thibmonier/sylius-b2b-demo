<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Webhook;

use App\Demo\Webhook\DemoWebhookTarget;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** US-029 / T-029-03: the only URL a demo webhook endpoint may have is the receiver of the container. */
final class DemoWebhookTargetTest extends TestCase
{
    public function testTheReceiverIsOnTheLoopbackAtThePortOfTheServer(): void
    {
        $target = new DemoWebhookTarget(8080);

        self::assertSame('http://127.0.0.1:8080/demo/webhooks/receive', $target->url());
        self::assertTrue($target->accepts('http://127.0.0.1:8080/demo/webhooks/receive'));
        self::assertSame('http://127.0.0.1:3000/demo/webhooks/receive', (new DemoWebhookTarget(3000))->url());
    }

    /** @return iterable<string, array{string}> */
    public static function refusedUrls(): iterable
    {
        yield 'external host' => ['https://hooks.partner.example/webhook'];
        yield 'external host on the receiver path' => ['http://hooks.partner.example:8080/demo/webhooks/receive'];
        yield 'look-alike host' => ['http://localhost.evil.example:8080/demo/webhooks/receive'];
        yield 'host with the loopback as a prefix' => ['http://127.0.0.1.evil.example:8080/demo/webhooks/receive'];
        yield 'credentials' => ['http://user:pass@127.0.0.1:8080/demo/webhooks/receive'];
        yield 'credentials hiding the host' => ['http://127.0.0.1:8080@evil.example/demo/webhooks/receive'];
        yield 'private address' => ['http://10.0.0.5:8080/demo/webhooks/receive'];
        yield 'metadata address' => ['http://169.254.169.254/latest/meta-data/'];
        yield 'other loopback spelling' => ['http://127.1:8080/demo/webhooks/receive'];
        yield 'localhost name' => ['http://localhost:8080/demo/webhooks/receive'];
        yield 'IPv6 loopback' => ['http://[::1]:8080/demo/webhooks/receive'];
        yield 'other port' => ['http://127.0.0.1:9000/demo/webhooks/receive'];
        yield 'no port' => ['http://127.0.0.1/demo/webhooks/receive'];
        yield 'https' => ['https://127.0.0.1:8080/demo/webhooks/receive'];
        yield 'other scheme' => ['ftp://127.0.0.1:8080/demo/webhooks/receive'];
        yield 'other path' => ['http://127.0.0.1:8080/demo/webhooks/other'];
        yield 'neighbour path' => ['http://127.0.0.1:8080/demo/webhooks/receive/'];
        yield 'query string' => ['http://127.0.0.1:8080/demo/webhooks/receive?next=http://evil.example'];
        yield 'fragment' => ['http://127.0.0.1:8080/demo/webhooks/receive#x'];
        yield 'upper-case path' => ['http://127.0.0.1:8080/DEMO/webhooks/receive'];
        yield 'empty' => [''];
    }

    #[DataProvider('refusedUrls')]
    public function testAnyOtherUrlIsRefused(string $url): void
    {
        self::assertFalse((new DemoWebhookTarget(8080))->accepts($url));
    }
}
