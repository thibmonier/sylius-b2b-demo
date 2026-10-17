<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\PunchOutSimulator;

use App\Demo\Http\InternalRequestToken;
use App\Demo\PunchOutSimulator\OciCredentials;
use App\Demo\PunchOutSimulator\OciEntryClient;
use App\Demo\PunchOutSimulator\SimulatorException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/** US-053: the OCI entry is posted to the shop itself, with the internal token and the IP of the visitor. */
final class OciEntryClientTest extends TestCase
{
    public function testTheEntryIsPostedToTheOciRouteOfTheBuyerAndTheAnswerIsKept(): void
    {
        $method = $url = $headers = $body = '';
        $redirects = null;
        $http = new MockHttpClient(function (string $requestMethod, string $requestUrl, array $options) use (&$method, &$url, &$headers, &$body, &$redirects): MockResponse {
            $method = $requestMethod;
            $url = $requestUrl;
            $headers = implode("\n", (array) $options['headers']);
            $body = (string) $options['body'];
            $redirects = $options['max_redirects'];

            return new MockResponse('', ['http_code' => 303, 'response_headers' => ['Location: /fr_FR/', 'Set-Cookie: PHPSESSID=abc; path=/; HttpOnly']]);
        });
        $client = new OciEntryClient($http, new InternalRequestToken('secret'));

        $result = $client->post(new OciCredentials('acme-oci', 'user', 'pass'), 'https://erp.acme.example/return?state=a.b', 'demo.example', 'https', '8080', '203.0.113.9');

        self::assertSame('POST', $method);
        self::assertSame('http://127.0.0.1:8080/punchout/oci/acme-oci', $url);
        self::assertSame(303, $result->status);
        self::assertSame('/fr_FR/', $result->location);
        self::assertSame('PHPSESSID', $result->cookies[0]->getName());
        self::assertStringContainsString('X-Demo-Internal: ' . (new InternalRequestToken('secret'))->value(), $headers);
        self::assertStringContainsString('X-Forwarded-For: 203.0.113.9', $headers);
        self::assertStringContainsString('Host: demo.example', $headers);
        self::assertSame(0, $redirects);
        self::assertStringContainsString('HOOK_URL=', $body);
    }

    public function testAnUnreachableShopIsASimulatorFailure(): void
    {
        $http = new MockHttpClient(static fn (): MockResponse => new MockResponse('', ['error' => 'refused']));

        $this->expectException(SimulatorException::class);
        $this->expectExceptionMessage('endpoint_unreachable');

        (new OciEntryClient($http, new InternalRequestToken('secret')))->post(new OciCredentials('c', 'u', 'p'), 'https://erp.acme.example/r', 'h', 'https', '80', '1.2.3.4');
    }
}
