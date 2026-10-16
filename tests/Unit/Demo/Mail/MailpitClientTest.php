<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Mail;

use App\Demo\Mail\MailpitClient;
use App\Demo\Mail\MailpitMessage;
use App\Demo\Mail\MailpitUnavailableException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/** US-028: read-only, GET-only Mailpit API client. */
final class MailpitClientTest extends TestCase
{
    public function testItListsIdsAndIgnoresMalformedOnes(): void
    {
        $http = new MockHttpClient(new MockResponse((string) json_encode(['messages' => [['ID' => 'abc123'], ['ID' => '../x'], ['nope' => 1], ['ID' => 'def-456']]])));

        self::assertSame(['abc123', 'def-456'], (new MailpitClient($http, 'http://mailpit:8025/'))->latestIds(50));
    }

    public function testItMapsAMessageAndUsesOnlyGet(): void
    {
        $methods = [];
        $http = new MockHttpClient(static function (string $method, string $url) use (&$methods): MockResponse {
            $methods[] = $method . ' ' . $url;

            return new MockResponse((string) json_encode([
                'Subject' => 'Hi', 'Text' => 'Body', 'HTML' => '<p>Body</p>', 'Date' => '2026-10-16T10:00:00Z',
                'From' => ['Name' => 'Shop', 'Address' => 'shop@demo.example'],
                'To' => [['Name' => '', 'Address' => 'a@demo.example'], ['Name' => 'B', 'Address' => 'b@demo.example']],
            ]));
        });

        $message = (new MailpitClient($http, 'http://mailpit:8025'))->find('abc123');

        self::assertInstanceOf(MailpitMessage::class, $message);
        self::assertSame('Shop <shop@demo.example>', $message->from);
        self::assertSame('a@demo.example, B <b@demo.example>', $message->to);
        self::assertSame('Body', $message->displayText());
        self::assertSame(['GET http://mailpit:8025/api/v1/message/abc123'], $methods);
    }

    public function testAMalformedIdNeverReachesMailpit(): void
    {
        $http = new MockHttpClient(static fn (): MockResponse => throw new \LogicException('No request expected.'));

        self::assertNull((new MailpitClient($http, 'http://mailpit:8025'))->find('../../api/v1/send'));
    }

    public function testAnUnknownMessageIsNull(): void
    {
        $http = new MockHttpClient(new MockResponse('{}', ['http_code' => 404]));

        self::assertNull((new MailpitClient($http, 'http://mailpit:8025'))->find('abc'));
    }

    public function testATransportErrorBecomesUnavailable(): void
    {
        $http = new MockHttpClient(new MockResponse('', ['error' => 'connection refused']));

        $this->expectException(MailpitUnavailableException::class);
        (new MailpitClient($http, 'http://mailpit:8025'))->latestIds(10);
    }

    public function testAServerErrorBecomesUnavailable(): void
    {
        $http = new MockHttpClient(new MockResponse('boom', ['http_code' => 500]));

        $this->expectException(MailpitUnavailableException::class);
        (new MailpitClient($http, 'http://mailpit:8025'))->latestIds(10);
    }

    public function testAnUnconfiguredClientIsUnavailable(): void
    {
        $client = new MailpitClient(new MockHttpClient(), '');

        self::assertFalse($client->isConfigured());
        $this->expectException(MailpitUnavailableException::class);
        $client->latestIds(10);
    }
}
