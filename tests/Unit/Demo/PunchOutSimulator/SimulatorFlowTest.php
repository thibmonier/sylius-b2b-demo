<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\PunchOutSimulator;

use App\Demo\PunchOutSimulator\BuyerCredentials;
use App\Demo\PunchOutSimulator\EndpointClient;
use App\Demo\PunchOutSimulator\OrderMessageParser;
use App\Demo\PunchOutSimulator\OrderRequestBuilder;
use App\Demo\PunchOutSimulator\ReceivedCart;
use App\Demo\PunchOutSimulator\ReceivedLine;
use App\Demo\PunchOutSimulator\ResponseParser;
use App\Demo\PunchOutSimulator\SetupRequestBuilder;
use App\Demo\PunchOutSimulator\SignedState;
use App\Demo\PunchOutSimulator\SimulatorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\OrderMessageWriter;
use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\OrderRequestReader;
use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\PayloadIdGenerator;
use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\SafeXmlLoader;
use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\SetupRequestReader;
use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\SetupResponseWriter;
use Tmonier\SyliusPunchoutCxmlPlugin\Transfer\TransferLine;

/**
 * US-027 — the cXML documents of the simulator are read by the real plugin readers, and the messages
 * of the plugin writers by the simulator parsers.
 */
final class SimulatorFlowTest extends TestCase
{
    public function testTheSetupRequestIsReadByThePluginReader(): void
    {
        $xml = (new SetupRequestBuilder(new MockClock('2026-10-16 10:00:00')))->build($this->credentials(), 'cookie-1', 'https://erp.acme.example/return');
        $document = (new SafeXmlLoader())->load($xml);
        $reader = new SetupRequestReader();

        $data = $reader->read($document);

        self::assertSame('PunchOutSetupRequest', $reader->requestName($document));
        self::assertSame('create', $data->operation);
        self::assertSame('cookie-1', $data->buyerCookie);
        self::assertSame('https://erp.acme.example/return', $data->browserFormPostUrl);
        self::assertSame('s3cr3t', $data->sharedSecret);
        self::assertSame(['DUNS', '123456789', 'AribaNetworkUserId', 'acme@ariba.example'], [$data->fromDomain, $data->fromIdentity, $data->senderDomain, $data->senderIdentity]);
        self::assertSame('2026-10-16T10:00:00+00:00', $data->timestamp);
    }

    public function testTheOrderRequestIsReadByThePluginReader(): void
    {
        $cart = $this->cart();
        $xml = (new OrderRequestBuilder(new MockClock('2026-10-16 10:00:00')))->build($this->credentials(), $cart, 'PO-2026-001');

        $data = (new OrderRequestReader())->read((new SafeXmlLoader())->load($xml));

        self::assertSame('PO-2026-001', $data->orderId);
        self::assertSame('new', $data->type);
        self::assertFalse($data->testMode);
        self::assertCount(2, $data->items);
        self::assertSame('FR', $data->shipTo->countryCode);
    }

    public function testTheCartPostedByThePluginIsParsed(): void
    {
        $lines = [
            new TransferLine('VAR-1', 'v1:VAR-1', 'Gants <nitrile>', 3, '12.50', 'EUR'),
            new TransferLine('VAR-2', 'v1:VAR-2', 'Casque', 1, '40.00', 'EUR', 'PK'),
        ];
        $xml = (new OrderMessageWriter())->write($lines, '77.50', 'EUR', 'cookie-1', 'payload@x', 'create');

        $cart = (new OrderMessageParser(new SafeXmlLoader()))->parseBase64(base64_encode($xml));

        self::assertSame('cookie-1', $cart->buyerCookie);
        self::assertSame(['EUR', '77.50'], [$cart->currency, $cart->total]);
        self::assertCount(2, $cart->lines);
        self::assertSame(['VAR-1', 'v1:VAR-1', 'Gants <nitrile>', 3, '12.50'], [$cart->lines[0]->supplierPartId, $cart->lines[0]->supplierPartAuxId, $cart->lines[0]->description, $cart->lines[0]->quantity, $cart->lines[0]->unitPrice]);
        self::assertSame('PK', $cart->lines[1]->unitOfMeasure);
    }

    public function testAnEmptyCartIsParsedAsEmpty(): void
    {
        $xml = (new OrderMessageWriter())->write([], '0.00', 'EUR', 'cookie-1', 'payload@x', 'create');

        self::assertTrue((new OrderMessageParser(new SafeXmlLoader()))->parse($xml)->isEmpty());
    }

    #[DataProvider('invalidMessages')]
    public function testAnInvalidReturnMessageIsRefused(string $encoded): void
    {
        $this->expectException(SimulatorException::class);
        $this->expectExceptionMessage('invalid_message');

        (new OrderMessageParser(new SafeXmlLoader()))->parseBase64($encoded);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidMessages(): iterable
    {
        yield 'not base64' => ['%%%'];
        yield 'empty' => [''];
        yield 'not xml' => [base64_encode('hello')];
        yield 'xxe' => [base64_encode('<?xml version="1.0"?><!DOCTYPE c [<!ENTITY x SYSTEM "file:///etc/passwd">]><cXML>&x;</cXML>')];
        yield 'other document' => [base64_encode('<cXML><Request/></cXML>')];
    }

    public function testTheSetupResponseGivesTheStartToken(): void
    {
        $token = str_repeat('a', 43);
        $xml = (new SetupResponseWriter(new PayloadIdGenerator()))->write('https://shop.example/punchout/start/' . $token);

        $response = (new ResponseParser(new SafeXmlLoader()))->parse($xml);

        self::assertTrue($response->isOk());
        self::assertSame($token, $response->startToken());
    }

    public function testAForeignStartUrlGivesNoToken(): void
    {
        $xml = (new SetupResponseWriter(new PayloadIdGenerator()))->write('https://evil.example/somewhere/' . str_repeat('a', 43));

        self::assertNull((new ResponseParser(new SafeXmlLoader()))->parse($xml)->startToken());
    }

    public function testAnErrorStatusIsReported(): void
    {
        $xml = (new SetupResponseWriter(new PayloadIdGenerator()))->writeError(401, 'Unauthorized');

        $response = (new ResponseParser(new SafeXmlLoader()))->parse($xml);

        self::assertFalse($response->isOk());
        self::assertSame(401, $response->code);
        self::assertNull($response->startToken());
    }

    public function testSignedStateIsRefusedWhenTamperedOrExpired(): void
    {
        $clock = new MockClock('2026-10-16 10:00:00');
        $state = new SignedState('secret', $clock);
        $value = $state->sign(['a' => 1]);

        self::assertSame(1, $state->verify($value)['a'] ?? null);
        self::assertNull($state->verify($value . 'x'));
        self::assertNull((new SignedState('other', $clock))->verify($value));
        self::assertNull($state->verify('garbage'));
        $clock->sleep(SignedState::LIFETIME + 1);
        self::assertNull($state->verify($value));
    }

    public function testTheEndpointClientPostsXmlToTheLocalServerWithThePublicHost(): void
    {
        $captured = [];
        $client = new EndpointClient(new MockHttpClient(function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = [$method, $url, $options['headers']];

            return new MockResponse('<cXML/>');
        }));

        self::assertSame('<cXML/>', $client->post('<x/>', 'demo.example', 'https', '8080'));
        self::assertSame(['POST', 'http://127.0.0.1:8080/punchout/cxml'], [$captured[0], $captured[1]]);
        self::assertContains('Host: demo.example', $captured[2]);
    }

    public function testTheEndpointClientReportsAnUnavailableEndpoint(): void
    {
        $client = new EndpointClient(new MockHttpClient(new MockResponse('', ['http_code' => 429])));

        $this->expectException(SimulatorException::class);
        $this->expectExceptionMessage('endpoint_status');

        $client->post('<x/>', 'demo.example', 'https', '80');
    }

    private function credentials(): BuyerCredentials
    {
        return new BuyerCredentials('DUNS', '123456789', 'AribaNetworkUserId', 'acme@ariba.example', 's3cr3t');
    }

    private function cart(): ReceivedCart
    {
        return new ReceivedCart('cookie-1', 'EUR', '77.50', [
            new ReceivedLine('VAR-1', 'v1:VAR-1', 'Gants', 3, '12.50', 'EUR', 'EA'),
            new ReceivedLine('VAR-2', 'v1:VAR-2', 'Casque', 1, '40.00', 'EUR', 'EA'),
        ]);
    }
}
