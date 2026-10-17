<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Webhook\DemoWebhookTarget;
use App\Entity\Webhook\ReceivedWebhook;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tmonier\SyliusWebhookOutboxPlugin\Entity\WebhookEndpointInterface;
use Tmonier\SyliusWebhookOutboxPlugin\Signature\WebhookSigner;

/**
 * US-029: the receiver of the demo webhooks, seen from the outside (real requests through the kernel). Run against a database
 * loaded with the `demo` fixture suite (the endpoint and its generated secret come from the fixture `demo_webhook_endpoint`).
 */
final class WebhookReceiverTest extends DemoAdminWebTestCase
{
    use EnvironmentSwitch;

    private const PATH = DemoWebhookTarget::PATH;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->db()->executeStatement('DELETE FROM app_received_webhook');
        $pool = self::getContainer()->get('cache.demo_rate_limiter');
        if ($pool instanceof AdapterInterface) {
            $pool->clear();
        }
        self::ensureKernelShutdown();
    }

    protected function tearDown(): void
    {
        self::bootKernel();
        $this->db()->executeStatement('DELETE FROM app_received_webhook');
        parent::tearDown();
    }

    public function testASignedWebhookIsStoredAndAnsweredDirectly(): void
    {
        $client = self::createClient();

        $this->deliver($client, 'evt_signed_1', 'order.placed');

        self::assertResponseStatusCodeSame(200);
        self::assertFalse($client->getResponse()->isRedirection());
        self::assertSame('received', $this->answerStatus($client));
        self::assertSame(1, $this->intValue('SELECT COUNT(*) FROM app_received_webhook WHERE webhook_id = ? AND event_type = ? AND signature_verified = 1', ['evt_signed_1', 'order.placed']));
    }

    public function testAnonymousCallIsAcceptedThroughThePrivateGateButTheRestOfTheShopIsNot(): void
    {
        $this->setEnvironment([
            'DEMO_BASIC_AUTH' => '1',
            'DEMO_BASIC_AUTH_USER' => 'visitor',
            'DEMO_BASIC_AUTH_PASSWORD_HASH' => self::gateHash('correct horse battery'),
        ]);
        $client = self::createClient();

        $this->deliver($client, 'evt_gate_1', 'order.paid');
        self::assertResponseStatusCodeSame(200);

        $client->request('GET', '/fr_FR/');
        self::assertResponseStatusCodeSame(401);
        $client->request('GET', self::PATH);
        self::assertResponseStatusCodeSame(401, 'only a POST skips the gate');
        foreach ([self::PATH . '/', self::PATH . 'er', '/demo/webhooks', '/demo/webhooks/received', '/admin/demo/webhooks'] as $neighbour) {
            $client->request('POST', $neighbour, content: '{}');
            self::assertResponseStatusCodeSame(401, $neighbour . ' stays behind the gate');
        }
        self::assertSame(1, $this->intValue('SELECT COUNT(*) FROM app_received_webhook'));
    }

    public function testTheLoopbackHostOfTheOutboxIsATrustedHost(): void
    {
        $this->setEnvironment(['TRUSTED_HOSTS' => '^shop\.test$']);
        $client = self::createClient();

        $this->deliver($client, 'evt_host_1', 'order.placed', server: ['HTTP_HOST' => '127.0.0.1:8080']);
        self::assertResponseStatusCodeSame(200);

        $this->deliver($client, 'evt_host_2', 'order.placed', server: ['HTTP_HOST' => 'evil.example']);
        self::assertResponseStatusCodeSame(400);
    }

    /** @return iterable<string, array{string}> */
    public static function tamperings(): iterable
    {
        yield 'body changed after signing' => ['body'];
        yield 'signed with another secret' => ['secret'];
        yield 'signature missing' => ['no_signature'];
        yield 'webhook-id missing' => ['no_id'];
        yield 'timestamp too old' => ['stale'];
        yield 'timestamp not a number' => ['bad_timestamp'];
        yield 'garbage signature' => ['garbage'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('tamperings')]
    public function testAnInvalidSignatureIsRefusedAndNothingIsStored(string $tampering): void
    {
        $client = self::createClient();
        $body = $this->envelope('evt_bad_1', 'order.placed');
        $headers = (new WebhookSigner())->headers('evt_bad_1', time(), $body, $this->secret());
        match ($tampering) {
            'body' => $body = str_replace('order.placed', 'order.cancelled', $body),
            'secret' => $headers = (new WebhookSigner())->headers('evt_bad_1', time(), $body, 'whsec_' . base64_encode(random_bytes(32))),
            'no_signature' => $headers['webhook-signature'] = '',
            'no_id' => $headers['webhook-id'] = '',
            'stale' => $headers = (new WebhookSigner())->headers('evt_bad_1', time() - 3600, $body, $this->secret()),
            'bad_timestamp' => $headers['webhook-timestamp'] = 'yesterday',
            default => $headers['webhook-signature'] = 'v1,Z2FyYmFnZQ==',
        };

        $client->request('POST', self::PATH, server: $this->serverHeaders($headers), content: $body);

        self::assertResponseStatusCodeSame(401);
        self::assertSame('invalid_signature', $this->answerStatus($client));
        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM app_received_webhook'));
    }

    public function testAReplayIsAnsweredButStoredOnce(): void
    {
        $client = self::createClient();

        $this->deliver($client, 'evt_replay_1', 'order.placed');
        $this->deliver($client, 'evt_replay_1', 'order.placed');

        self::assertResponseStatusCodeSame(200);
        self::assertSame('duplicate', $this->answerStatus($client));
        self::assertSame(1, $this->intValue('SELECT COUNT(*) FROM app_received_webhook'));
    }

    public function testAnOversizedBodyIsRefusedBeforeAnythingIsStored(): void
    {
        $client = self::createClient();

        $this->deliver($client, 'evt_big_1', 'order.placed', ['blob' => str_repeat('x', 300_000)]);

        self::assertResponseStatusCodeSame(413);
        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM app_received_webhook'));
    }

    public function testASignedBodyThatIsNotAnEventIsRefused(): void
    {
        $client = self::createClient();
        $body = '["not", "an", "envelope"]';

        $client->request('POST', self::PATH, server: $this->serverHeaders((new WebhookSigner())->headers('evt_shape_1', time(), $body, $this->secret())), content: $body);

        self::assertResponseStatusCodeSame(400);
        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM app_received_webhook'));
    }

    public function testALongPayloadIsStoredTruncated(): void
    {
        $client = self::createClient();

        $this->deliver($client, 'evt_long_1', 'order.placed', ['note' => str_repeat('é', 20_000)]);

        self::assertResponseStatusCodeSame(200);
        $stored = $this->stringValue('SELECT payload FROM app_received_webhook WHERE webhook_id = ?', ['evt_long_1']);
        self::assertLessThanOrEqual(ReceivedWebhook::MAX_PAYLOAD_BYTES, \strlen($stored));
        self::assertTrue(mb_check_encoding($stored, 'UTF-8'), 'cut on a character boundary');
        self::assertSame(1, $this->intValue('SELECT payload_truncated FROM app_received_webhook WHERE webhook_id = ?', ['evt_long_1']));
    }

    public function testTheReceiverHasItsOwnRateLimit(): void
    {
        $this->setEnvironment(['DEMO_RATE_LIMIT_WEBHOOK_RECEIVER' => '2']);
        $client = self::createClient();

        $this->deliver($client, 'evt_rate_1', 'order.placed');
        $this->deliver($client, 'evt_rate_2', 'order.placed');
        self::assertResponseStatusCodeSame(200);
        $this->deliver($client, 'evt_rate_3', 'order.placed');

        self::assertResponseStatusCodeSame(429);
        self::assertTrue($client->getResponse()->headers->has('Retry-After'));
        self::assertSame(2, $this->intValue('SELECT COUNT(*) FROM app_received_webhook'));
    }

    public function testAVisitorCannotExhaustTheQuotaOfTheDeliveriesOfTheContainer(): void
    {
        $this->setEnvironment(['DEMO_RATE_LIMIT_WEBHOOK_RECEIVER' => '2']);
        $client = self::createClient();

        for ($i = 0; $i < 3; ++$i) {
            $client->request('POST', self::PATH, server: ['REMOTE_ADDR' => '203.0.113.9'], content: '{}');
        }
        self::assertResponseStatusCodeSame(429, 'the visitor is limited');

        $this->deliver($client, 'evt_quota_1', 'order.placed', server: ['REMOTE_ADDR' => '127.0.0.1']);

        self::assertResponseStatusCodeSame(200);
        self::assertSame(1, $this->intValue('SELECT COUNT(*) FROM app_received_webhook'));
    }

    public function testTheStoreKeepsTheLatestWebhooksOnly(): void
    {
        $client = self::createClient();
        $this->db()->executeStatement('DELETE FROM app_received_webhook');
        for ($i = 0; $i < 205; ++$i) {
            $this->db()->executeStatement(
                'INSERT INTO app_received_webhook (id, received_at, payload, payload_truncated, webhook_id, event_type, signature_verified) VALUES (?, ?, ?, 0, ?, ?, 1)',
                [random_bytes(16), (new \DateTimeImmutable('2026-01-01 00:00:00'))->modify('+' . $i . ' seconds')->format('Y-m-d H:i:s'), '{}', 'old_' . $i, 'order.placed'],
            );
        }

        $this->deliver($client, 'evt_new_1', 'order.placed');

        self::assertSame(200, $this->intValue('SELECT COUNT(*) FROM app_received_webhook'));
        self::assertSame(1, $this->intValue('SELECT COUNT(*) FROM app_received_webhook WHERE webhook_id = ?', ['evt_new_1']));
        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM app_received_webhook WHERE webhook_id = ?', ['old_0']));
    }

    /** @return iterable<string, array{string, string, string}> */
    public static function adminLocales(): iterable
    {
        yield 'French' => ['fr_FR', 'Webhooks reçus', 'Vérifiée'];
        yield 'English' => ['en_US', 'Received webhooks', 'Verified'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('adminLocales')]
    public function testSophieSeesTheWebhookInThePage(string $locale, string $title, string $verified): void
    {
        $client = $this->sophieClient($locale);
        $this->deliver($client, 'evt_page_1', 'order.placed', ['number' => '000000042']);

        $page = $client->request('GET', '/admin/demo/webhooks');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString($title, $page->filter('.page-title')->first()->text());
        self::assertCount(1, $page->filter('[data-demo-webhook-row]'));
        self::assertSame('order.placed', trim($page->filter('[data-demo-webhook-row] code')->first()->text()));
        self::assertSame($verified, trim($page->filter('[data-demo-webhook-signature="verified"]')->text()));
        self::assertCount(1, $client->request('GET', '/admin/')->filter('a[href="/admin/demo/webhooks"]'), 'linked from the menu');

        $detail = $client->click($page->filter('[data-demo-webhook-row] a')->link());
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('000000042', $detail->filter('[data-demo-webhook-payload]')->text());
        self::assertSame('order.placed', trim($detail->filter('[data-demo-webhook-type]')->text()));
    }

    public function testThePageExplainsItselfWhenNothingWasReceived(): void
    {
        $client = $this->sophieClient();

        $client->request('GET', '/admin/demo/webhooks');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-demo-webhooks-empty]');
        self::assertSelectorExists('[data-demo-webhooks-note]');
    }

    public function testWhatAVisitorTypedIsEscaped(): void
    {
        $client = $this->sophieClient();
        $this->deliver($client, 'evt_xss_1', 'order.<img src=x onerror=alert(1)>', ['customer' => ['note' => '<script>alert("xss")</script>']]);
        self::assertResponseStatusCodeSame(200);

        $list = $client->request('GET', '/admin/demo/webhooks');
        $html = (string) $client->getResponse()->getContent();
        self::assertStringNotContainsString('<img src=x', $html);
        self::assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $html);
        self::assertCount(0, $list->filter('img[src="x"]'));

        $detail = $client->click($list->filter('[data-demo-webhook-row] a')->link());
        $html = (string) $client->getResponse()->getContent();
        self::assertStringNotContainsString('<script>alert("xss")', $html);
        self::assertStringContainsString('&lt;script&gt;alert(', $html);
        self::assertCount(0, $detail->filter('script:contains("xss")'));
    }

    public function testThePageIsForTheBackOfficeOnly(): void
    {
        $client = self::createClient();

        $client->request('GET', '/admin/demo/webhooks');

        self::assertResponseRedirects();
        self::assertStringContainsString('/admin/login', (string) $client->getResponse()->headers->get('Location'));
        self::assertStringNotContainsString('Webhook', (string) $client->getResponse()->getContent());
    }

    public function testAnUnknownWebhookIs404(): void
    {
        $client = $this->sophieClient();

        $client->request('GET', '/admin/demo/webhooks/0190a1b2-c3d4-7e5f-8a9b-0c1d2e3f4a5b');

        self::assertResponseStatusCodeSame(404);
    }

    /** @return iterable<string, array{string}> */
    public static function refusedUrls(): iterable
    {
        yield 'external host' => ['https://hooks.partner.example/webhook'];
        yield 'look-alike host' => ['http://localhost.evil.example:8080/demo/webhooks/receive'];
        yield 'internal address' => ['http://10.0.0.5:8080/demo/webhooks/receive'];
        yield 'metadata address' => ['http://169.254.169.254/latest/meta-data/'];
        yield 'other scheme' => ['ftp://127.0.0.1:8080/demo/webhooks/receive'];
        yield 'credentials' => ['http://user:pass@127.0.0.1:8080/demo/webhooks/receive'];
        yield 'other route of the demo' => ['http://127.0.0.1:8080/punchout/cxml'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('refusedUrls')]
    public function testNoOtherUrlThanTheReceiverCanBeSubscribed(string $url): void
    {
        self::bootKernel();
        $endpoint = $this->newEndpoint($url);

        $violations = $this->validator()->validate($endpoint, groups: ['sylius']);

        self::assertGreaterThan(0, $violations->count(), $url . ' must be refused by the admin validation');
    }

    public function testTheReceiverItselfCanBeSubscribed(): void
    {
        self::bootKernel();
        $target = self::getContainer()->get(DemoWebhookTarget::class);
        self::assertInstanceOf(DemoWebhookTarget::class, $target);

        $violations = $this->validator()->validate($this->newEndpoint($target->url()), groups: ['sylius']);

        self::assertCount(0, $violations);
    }

    private function newEndpoint(string $url): WebhookEndpointInterface
    {
        $class = self::getContainer()->getParameter('tmonier_webhook.model.webhook_endpoint.class');
        self::assertIsString($class);
        $endpoint = new $class();
        self::assertInstanceOf(WebhookEndpointInterface::class, $endpoint);
        $endpoint->setName('test');
        $endpoint->setUrl($url);

        return $endpoint;
    }

    private function validator(): ValidatorInterface
    {
        $validator = self::getContainer()->get('validator');
        self::assertInstanceOf(ValidatorInterface::class, $validator);

        return $validator;
    }

    /** @param array<string, mixed> $data */
    private function envelope(string $id, string $type, array $data = []): string
    {
        return json_encode(['id' => $id, 'type' => $type, 'version' => 1, 'timestamp' => gmdate('Y-m-d\TH:i:s\Z'), 'channel' => 'B2B', 'data' => $data], \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param array<string, mixed>  $data
     * @param array<string, string> $server
     */
    private function deliver(KernelBrowser $client, string $id, string $type, array $data = [], array $server = []): void
    {
        $body = $this->envelope($id, $type, $data);
        $client->request('POST', self::PATH, server: $this->serverHeaders((new WebhookSigner())->headers($id, time(), $body, $this->secret())) + $server, content: $body);
    }

    private function secret(): string
    {
        $secret = $this->db()->fetchOne('SELECT secret FROM tmonier_webhook_endpoint WHERE name = ?', ['Démo — récepteur interne']);
        self::assertIsString($secret, 'the demo fixtures must be loaded');

        return $secret;
    }

    /**
     * @param array<string, string> $headers
     *
     * @return array<string, string>
     */
    private function serverHeaders(array $headers): array
    {
        $server = ['CONTENT_TYPE' => 'application/json'];
        foreach ($headers as $name => $value) {
            $server['HTTP_' . strtoupper(str_replace('-', '_', $name))] = $value;
        }

        return $server;
    }

    private function answerStatus(KernelBrowser $client): string
    {
        $answer = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($answer);

        return (string) ($answer['status'] ?? '');
    }
}
